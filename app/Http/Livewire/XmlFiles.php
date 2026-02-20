<?php

namespace App\Http\Livewire;

use App\Models\DeletedFactura;
use App\Models\Factura;
use App\Models\XmlFile;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Traits\CartTrait;
use Illuminate\Support\Facades\DB;
use App\Services\SignatureService;
use App\Services\SriSoapService;
use App\Services\XmlGeneratorService;

class XmlFiles extends Component
{


    use WithPagination;
    use WithFileUploads;
    use CartTrait;



    public $fact_id='', $secuencial ='', $customer='', $directorio='', $estado;
    public $action = 'Listado', $componentName='FACTURAS PENDIENTES DE PROCESAR', $search, $form = false;
    private $pagination =20;
    protected $paginationTheme='tailwind';

    // public function render()
    // {
    //     $facturas = XmlFile::where('estado', '!=', 'aprobado')->get();
    //     return view('livewire.reprocesar.component', compact('facturas' ))->layout('layouts.theme.app');
    // }
    public function render()
    {
        if (strlen($this->search) > 0)
            $info = XmlFile::where('secuencial', 'like', "%{$this->search}%")->whereHas('factura')->paginate($this->pagination);
        else
            $info = XmlFile::where('estado','!=','autorizado')->whereHas('factura')->paginate($this->pagination);


        return view('livewire.reprocesar.component', ['xmls' => $info])
            ->layout('layouts.theme.app');
    }



    public function retry(XmlFile $xmlFile){

        // Instanciar servicios
        $signatureService = app(SignatureService::class);
        $sriService = app(SriSoapService::class);
        $xmlService = app(XmlGeneratorService::class);

        $factura = Factura::find($xmlFile->factura_id);
        
        if (!$factura) {
            $this->noty('Factura no encontrada.', 'noty', 'error');
            return;
        }

        $estado = strtolower(trim($xmlFile->estado));
        $empresa = $factura->empresa();

        try {
            $xmlName = $factura->customer->valueidenti . '_' . $factura->secuencial; 
            $datePath = $factura->created_at->format('Y/m/');
            $fullXmlName = $datePath . $xmlName;

            // PRE-VALIDACIÓN PROACTIVA CON EL SRI
            $sriStatus = $sriService->consultarEstado($factura->claveAcceso);
            
            if (isset($sriStatus['estado'])) {
                if ($sriStatus['estado'] === 'AUTORIZADO') {
                    $factura->fechaAutorizacion = Carbon::parse($sriStatus['fechaAutorizacion']);
                    $factura->numeroAutorizacion = $sriStatus['numeroAutorizacion'];
                    $factura->save();
                    
                    $this->updateFact($factura);
                    $this->noty("¡Éxito! El comprobante ya estaba autorizado en el SRI. Se sincronizaron los datos localmente.");
                    return;
                } elseif ($sriStatus['estado'] === 'DEVUELTA' || $sriStatus['estado'] === 'NO AUTORIZADO') {
                    $errorFull = implode(' | ', $sriStatus['mensajes'] ?? []);
                    if (str_contains(strtoupper($errorFull), 'CLAVE ACCESO REGISTRADA') || 
                        str_contains(strtoupper($errorFull), 'EN PROCESAMIENTO')) {
                        $this->noty("La clave ya está en el SRI. Intentando recuperar la respuesta completa...", 'noty', 'warning');
                        // Continúa al flujo de recuperación normal
                    } else {
                        $xmlFile->update(['error' => $errorFull]);
                    }
                }
            }
            
            // 1. FORCE RE-SIGNING FOR REJECTED INVOICES
            if ($estado === 'devuelto' || $estado === 'no_firmado') {
                $signedXmlPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/firmados')->path($fullXmlName . '.xml');
                if (file_exists($signedXmlPath)) {
                    unlink($signedXmlPath);
                } else {
                    // Fallback to root (older versions)
                    $rootSignedPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/firmados')->path($xmlName . '.xml');
                    if (file_exists($rootSignedPath)) unlink($rootSignedPath);
                }
                $estado = 'creado'; // Switch to creado flow below
            }

            // 2. SIGNING FLOW
            if ($estado === 'creado') {
                // Check if file exists in Dated Path or Root
                $sourcePath = \Illuminate\Support\Facades\Storage::disk('comprobantes/no_firmados')->path($fullXmlName . '.xml');
                $finalNameForSigning = $fullXmlName;
                
                if (!file_exists($sourcePath)) {
                    $rootPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/no_firmados')->path($xmlName . '.xml');
                    if (file_exists($rootPath)) {
                        $finalNameForSigning = $xmlName;
                    } else {
                         // Regenerate if totally missing
                         $xmlService->generate(
                            $factura->id,
                            ($factura->customer->typeidenti == 'ruc' ? '04' : '05'),
                            $factura->customer->businame,
                            $factura->customer->valueidenti,
                            $factura->customer->address,
                            $factura->subtotal,
                            $factura->descuento,
                            $factura->total,
                            $this->reconstructCartFromFactura($factura),
                            $factura->secuencial,
                            $factura->claveAcceso,
                            $factura->impuestos->toArray(), 
                            $empresa,
                            $factura->created_at->format('d/m/Y')
                        );
                        // Generator returns relative path without Y/m sometimes? No, it uses it.
                        // Let's ensure we use the fullXmlName for the signing call
                    }
                }

                $signedXmlObj = $signatureService->firmarFactura($finalNameForSigning, $factura->id, $empresa);
                $sriService->enviarAlSri($signedXmlObj, $factura->id);
                $this->checkAuthorization($sriService, $signedXmlObj, $factura);
            }
            // 3. SENDING FLOW (ALREADY SIGNED BUT NOT SENT)
            elseif ($estado === 'firmado' || $estado === 'no_enviado') {
                $signedXmlPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/firmados')->path($fullXmlName . '.xml');
                $finalSignedName = $fullXmlName;
                
                 if (!file_exists($signedXmlPath)) {
                      $rootSignedPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/firmados')->path($xmlName . '.xml');
                      if (file_exists($rootSignedPath)) {
                          $signedXmlPath = $rootSignedPath;
                          $finalSignedName = $xmlName;
                      } else {
                          $xmlFile->update(['estado' => 'creado']);
                          $this->noty('Archivo firmado no encontrado. Reintente para firmar de nuevo.', 'noty', 'warning');
                          return;
                      }
                 }

                $content = file_get_contents($signedXmlPath);
                $signedXmlObj = new \stdClass();
                $signedXmlObj->key = $factura->claveAcceso;
                $signedXmlObj->base64 = base64_encode($content);
                $signedXmlObj->xmlContent = $content;
                $signedXmlObj->signedFileName = $finalSignedName . '.xml';

                $sriService->enviarAlSri($signedXmlObj, $factura->id);
                $this->checkAuthorization($sriService, $signedXmlObj, $factura);
            }
            // 4. AUTHORIZATION FLOW (ALREADY SENT)
            elseif ($estado === 'enviado' || $estado === 'no_autorizado' || $estado === 'en_proceso') {
                $signedXmlObj = new \stdClass();
                $signedXmlObj->key = $factura->claveAcceso;
                $signedXmlObj->signedFileName = $fullXmlName . '.xml';
                
                // Try to locate the file in Dated Path, Root, or fallback to Enviados
                $locations = [
                    'comprobantes/firmados/' . $fullXmlName . '.xml',
                    'comprobantes/firmados/' . $xmlName . '.xml',
                    'comprobantes/enviados/' . $fullXmlName . '.xml',
                    'comprobantes/enviados/' . $xmlName . '.xml'
                ];

                foreach ($locations as $loc) {
                    $disk = str_starts_with($loc, 'comprobantes/firmados') ? 'comprobantes/firmados' : 'comprobantes/enviados';
                    $rel = str_replace($disk . '/', '', $loc);
                    $abs = \Illuminate\Support\Facades\Storage::disk($disk)->path($rel);
                    
                    if (file_exists($abs)) {
                        $signedXmlObj->xmlContent = file_get_contents($abs);
                        $signedXmlObj->signedFileName = $rel; // Update to correct relative path
                        break;
                    }
                }
                
                if (!isset($signedXmlObj->xmlContent)) {
                    $this->noty("No se encontró el contenido XML para verificar la autorización.", 'noty', 'error');
                    return;
                }
                
                $this->checkAuthorization($sriService, $signedXmlObj, $factura);
            }
 else {
                 $this->noty("Estado no reconocido: $estado", 'noty', 'error');
            }
            
            $this->emit('refreshReprocesarCount');

        } catch (\Throwable $th) {
            $this->noty('Error al reprocesar: ' . $th->getMessage(), 'noty', 'error');
            Log::error("Error reprocessing XML: " . $th->getMessage());
        }
    }

    private function checkAuthorization($sriService, $signedXmlObj, $factura)
    {
         $authResponse = $sriService->consultarAutorizacion($signedXmlObj, $factura->id);

        if (isset($authResponse['estado']) && $authResponse['estado'] == 'AUTORIZADO') {
                $factura->fechaAutorizacion = Carbon::now(); 
                $factura->numeroAutorizacion = $authResponse['numeroAutorizacion'];
                $factura->save();
                
                $this->updateFact($factura); // Redirige al PDF
        } else {
                $estado = $authResponse['estado'] ?? 'DESCONOCIDO';
                $mensaje = $authResponse['mensaje'] ?? ''; // Mensaje de error si hay
                $this->noty("Factura enviada. Estado SRI: $estado. $mensaje", 'noty', 'warning');
        }
    }

    // Helper para reconstruir items si hay que regenerar XML (caso raro)
    private function reconstructCartFromFactura($factura) {
        $items = [];
        foreach($factura->detalles as $det) {
             // Reconstruir estructura de Array para XmlGeneratorService
             $item = [];
             $item['id'] = $det->product_id;
             $item['name'] = $det->descripcion;
             $item['qty'] = $det->cantidad;
             $item['price'] = $det->precioUnitario;
             $item['descuento'] = $det->descuento;
             
             // Reconstruir impuestos desde el producto
             $product = \App\Models\Product::with('impuestos')->find($det->product_id);
             $item['impuestos'] = [];
             
             if($product && $product->impuestos) {
                 foreach($product->impuestos as $tax) {
                     $item['impuestos'][] = [
                        'codigo' => $tax->codigo, 
                        'codigo_porcentaje' => $tax->codigo_porcentaje, 
                        'porcentaje' => $tax->porcentaje,
                     ];
                 }
             }
             
             $items[] = $item;
        }
        return $items; // Retornar array simple
    }


    public function updateFact(Factura $factura) {
        $factura->fechaAutorizacion =  Carbon::now();
        $factura->numeroAutorizacion =  $factura->claveAcceso;
        $factura->save();
        $url  =  route('descargar-pdf',['factura' => $factura->id]);
        $this->noty('FACTURA GENERADA  CORRECTAMENTE !!!!!!');
        return redirect()->to($url);
    }



    public function noty($msg, $eventName = 'noty', $reset = true, $action =""){
        $this->dispatchBrowserEvent($eventName, ['msg'=>$msg, 'type' => 'success', 'action' => $action ]);

        //if($reset) $this->resetUI();
    }




    public  function confirmDelete(XmlFile $xml ){
        //dd($xml->factura_id);
        $this->dispatchBrowserEvent('swal:confirm',[
                'facturaId' => $xml->factura_id,
        ]);}


        protected $listeners = ['delete' => 'delete'];

        public function delete($factura_id)
        {
            // Buscar la factura con todos sus campos, incluso si está eliminada


                // Verificar que la factura se ha recuperado correctamente
                //dd($factura);
            try {
                DB::transaction(function () use ($factura_id) {
                    // Recuperar la factura con relaciones necesarias
                    $factura = Factura::withTrashed()->findOrFail($factura_id);
                    //dd($factura->xmlFile->error);
                    if (!$factura) {
                        throw new \Exception("Factura no encontrada con ID {$factura_id}");
                    }

                    if($factura->xmlFile->error == null){
                        $estado = 'ANULADA SIN PROCESO SRI';
                    }
                    else{
                        $estado = $factura->xmlFile->error;
                    }

                    // Restaurar stock antes de eliminar la factura
                    $this->restoreStockFromFacturas($factura);

                    // Guardar en la tabla DeletedFactura
                    DeletedFactura::create([
                        'factura_id' => $factura->id,
                        'secuencial' => $factura->secuencial,
                        'cliente' => $factura->customer->businame ?? 'N/A',
                        'ruc_cliente' => $factura->customer->valueidenti ?? 'N/A',
                        'correo_cliente' => $factura->customer->email ?? 'N/A',
                        'fecha_emision' => $factura->created_at->toDateString(),
                        'clave_acceso' => $factura->claveAcceso,
                        'estado' => $estado
                    ]);

                    // Eliminar archivos XML asociados a la factura
                    XmlFile::where('factura_id', $factura->id)->delete();

                    // Finalmente, eliminar la factura (soft delete)
                    $factura->delete();
                });

                // Notificar éxito
                $this->noty('Factura eliminada con éxito');
            } catch (\Throwable $th) {
                // Registrar error en logs
                \Log::error("Error al eliminar la factura ID {$factura_id}: " . $th->getMessage());

                // Notificar error
                $this->noty('No se pudo eliminar la factura. Error: ' . $th->getMessage());
            }
        }


}
