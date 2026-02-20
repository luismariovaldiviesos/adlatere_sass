<?php

namespace App\Http\Livewire;

use App\Http\Controllers\PdfController;
use App\Models\DeletedFactura;
use App\Models\Factura;
use App\Models\Product;
use App\Models\Setting;
use App\Models\XmlFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Traits\CartTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceList extends Component
{
    use WithPagination;
    use WithFileUploads;
    use CartTrait;

    public $fact_id='', $secuencial ='', $customer='', $directorio='', $estado, $annulmentDays;
    public $motivoNC = ''; // New Property
    public $selectedFacturaId = null; // New Property

    public  $factura_detalle ;
    public $action = 'Listado', $componentName='LISTADO DE FACTURAS', $search, $form = false;
    private $pagination =20;
    protected $paginationTheme='tailwind';


    public function mount()
    {
        $this->annulmentDays = Setting::first()?->annulment_days ?? 15;
    }

    public function render()
{
    // Si hay un término de búsqueda, se filtra por secuencial o cliente.
    if (strlen($this->search) > 0) {
        $info = Factura::with('notasCredito') // Eager load
            ->whereNotNull('numeroAutorizacion') // Solo facturas aprobadas por el SRI
            ->where('codDoc', '01') // Filtrar solo facturas
            ->where(function ($query) {
                $query->where('secuencial', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', function ($q) {
                        $q->where('businame', 'like', "%{$this->search}%");
                    })
                    ->orWhereDate('fechaAutorizacion', 'like', "%{$this->search}%");
            })
            ->orderBy('secuencial', 'desc')
            ->paginate($this->pagination);
    } else {
        // Si no hay término de búsqueda, solo traer facturas aprobadas
        $info = Factura::with('notasCredito') // Eager load
            ->whereNotNull('numeroAutorizacion')
            ->where('codDoc', '01')
            ->orderBy('secuencial', 'desc')
            ->paginate($this->pagination);
    }


    // Devuelve las facturas al componente de Livewire para la vista.
    return view('livewire.listadofacturas.component', ['facturas' => $info])
        ->layout('layouts.theme.app');
}

public function noty($msg, $eventName= 'noty', )
{
    $this->dispatchBrowserEvent($eventName, ['msg' => $msg, 'type' => 'success']);
}





    function retry(Factura $factura)  {
        try {
            $pdfcontroller  =  New PdfController();
            $pdfcontroller->enviarFacturea($factura);
            $this->noty('PDF FACTURA REENVIADA  CORRECTAMENTE !!!!!!');
        } catch (\Exception $e) {
            Log::error("[RETRY_ERROR] Error en reenvío de factura: " . $e->getMessage());
            $this->noty('ERROR AL REENVIAR: ' . $e->getMessage(), 'noty', 'error');
        }
    }

    function downloadFiles(Factura $factura)  {

        $pdf_name =  $factura->customer->businame.'_'.$factura->secuencial;
        // The PDF is stored in YYYY/MM/ subdirectories
        $relativePath = $factura->created_at->format('Y') . '/' . $factura->created_at->format('m') . '/';
        $pdfPath = \Illuminate\Support\Facades\Storage::disk('comprobantes/pdfs')->path($relativePath . $pdf_name.'.pdf');
        
        if (!file_exists($pdfPath)) {
            // Fallback: If not found, maybe it's an old file in root? Or try to regenerate.
             $pdfPathOld = \Illuminate\Support\Facades\Storage::disk('comprobantes/pdfs')->path($pdf_name.'.pdf');
             if(file_exists($pdfPathOld)) {
                 $pdfPath = $pdfPathOld;
             } else {
                 // Regenerate if missing
                 $pdfController = new PdfController();
                 $pdfController->generatePdf($factura, false);
                 // Re-check path
                 if (!file_exists($pdfPath)) {
                     $this->noty('El archivo PDF no se encuentra y no se pudo regenerar.', 'noty', 'error');
                     return;
                 }
             }
        }
        
        return response()->download($pdfPath);

    }

    public  function confirmDelete(Factura $factura){
        //dd($factura->id);
        $this->dispatchBrowserEvent('swal:confirm',[
                'facturaId' => $factura->id
        ]);
    }

    public  function confirmNC(Factura $factura){
        
        // VALIDACIÓN SRI RESOLUCIÓN NAC-DGERCGC25-00000014
        
        // 1. Bloqueo Consumidor Final
        if ($factura->customer->valueidenti === '9999999999999' || strtoupper($factura->customer->businame) === 'CONSUMIDOR FINAL') {
            $this->noty('BLOQUEADO: No se puede emitir Nota de Crédito a Consumidor Final (Norma SRI NAC-DGERCGC25-00000014)', 'noty', 'error');
            return;
        }

        // 2. Bloqueo > 12 meses
        $diasDiferencia = now()->diffInDays($factura->created_at);
        if ($diasDiferencia > 365) {
            $this->noty("BLOQUEADO: La factura tiene más de un año ($diasDiferencia días). Plazo máximo NC: 365 días.", 'noty', 'error');
            return;
        }

        $this->selectedFacturaId = $factura->id;
        $this->motivoNC = 'Devolución Total'; // Default reason
        $this->dispatchBrowserEvent('open-modal-nc');
    }

    protected $listeners =
        [
            'delete' => 'delete',
            'nc' => 'nc',

        ];

    function emitirNC (){
        $this->validate([
            'motivoNC' => 'required|min:5',
            'selectedFacturaId' => 'required|exists:facturas,id'
        ]);

        $facturaOriginal = Factura::find($this->selectedFacturaId);
        
        // Transaction
        DB::beginTransaction();
        try {
            // 1. Restore Stock (Since it is a total return)
            $this->restoreStockFromFacturas($facturaOriginal);
            
            // 2. Create NC Record
            $newSecuencial = $facturaOriginal->secuencial('04'); 
            $newClaveAcceso = $facturaOriginal->claveAcceso('04');

            $nc = Factura::create([
                'secuencial' => $newSecuencial,
                'codDoc' => '04', // Nota de Crédito
                'claveAcceso' => $newClaveAcceso,
                'customer_id' => $facturaOriginal->customer_id,
                'user_id' => Auth()->user()->id,
                'subtotal' => $facturaOriginal->subtotal,
                'descuento' => $facturaOriginal->descuento,
                'total' => $facturaOriginal->total,
                'formaPago' => $facturaOriginal->formaPago,
                'factura_modificada_id' => $facturaOriginal->id,
                'motivo_nc' => $this->motivoNC,
                //'fechaAutorizacion' => null, // Will be set after Auth
            ]);

            // 3. Copy Details
            foreach($facturaOriginal->detalles as $detalle) {
                \App\Models\DetalleFactura::create([
                    'factura_id' => $nc->id,
                    'product_id' => $detalle->product_id,
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->descripcion,
                    'precioUnitario' => $detalle->precioUnitario,
                    'descuento' => $detalle->descuento,
                    'total' => $detalle->total
                ]);
            }
            
            // 4. Copy Taxes
             foreach($facturaOriginal->impuestos as $imp) {
                \App\Models\FacturaImpuesto::create([
                    'factura_id' => $nc->id,
                    'nombre_impuesto' => $imp->nombre_impuesto,
                    'codigo_impuesto' => $imp->codigo_impuesto,
                    'codigo_porcentaje' => $imp->codigo_porcentaje,
                    'base_imponible' => $imp->base_imponible,
                    'valor_impuesto' => $imp->valor_impuesto,
                ]);
            }

            DB::commit();

            // 5. Generate XML, Sign, Send (Async or Sync)
            // We can reuse the logic from Facturas component but adapted for NC
            // For now, let's try to call the Service.
            
            $xmlService = app(\App\Services\XmlGeneratorService::class);
            $signatureService = app(\App\Services\SignatureService::class);
            $sriService = app(\App\Services\SriSoapService::class);

             $xmlFile = $xmlService->generateNC($nc, $facturaOriginal);
             
             $signedXmlObj = $signatureService->firmarFactura($xmlFile, $nc->id, $nc->empresa());
             $sriService->enviarAlSri($signedXmlObj, $nc->id);
             $authResponse = $sriService->consultarAutorizacion($signedXmlObj, $nc->id);

             if (isset($authResponse['estado']) && $authResponse['estado'] == 'AUTORIZADO') {
                 $nc->fechaAutorizacion = \Carbon\Carbon::now();
                 $nc->numeroAutorizacion = $authResponse['numeroAutorizacion'];
                 $nc->save();
                 
                  // Enviar correo de la NC (Sync para evitar config de colas por ahora)
                  try {
                       // Generate PDF first (failsafe)
                       $pdfController = new PdfController();
                       $pdfController->generatePdf($nc, false); // Save to disk, no response/echo
                       
                       \App\Jobs\SendInvoiceEmail::dispatchSync($nc);
                  } catch (\Exception $e) {
                      \Log::error('Error enviando correo NC: ' . $e->getMessage());
                  }

                 $this->noty('NOTA DE CRÉDITO AUTORIZADA Y ENVIADA');
             } else {
                 $this->noty('NC Generada pero NO AUTORIZADA: ' . ($authResponse['estado'] ?? 'Error'), 'noty', 'warning');
             }

             $this->dispatchBrowserEvent('close-modal-nc');
             $this->resetUI();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->noty('Error al emitir NC: ' . $e->getMessage(), 'noty', 'error');
           // \Log::error($e);
        }

    }

    function delete(Factura $factura)
    {

        try {
            DB::transaction(function () use ($factura) {

                // Restaurar stock antes de eliminar la factura
               $this->restoreStockFromFacturas($factura);
                DeletedFactura::create([
                    'factura_id' => $factura->id,
                    'secuencial' => $factura->secuencial,
                    'cliente' => $factura->customer->businame,
                    'ruc_cliente' => $factura->customer->valueidenti,
                    'correo_cliente' => $factura->customer->email,
                    'fecha_emision' => $factura->created_at->toDateString(),
                    'clave_acceso' => $factura->claveAcceso,
                ]);

                // Soft delete de la factura
                $factura->delete();
                //eliminamos de xml_files
                XmlFile::where('factura_id', $factura->id)->delete();
            });

            // Notificar éxito
            $this->noty('Factura eliminada con éxito');
        } catch (\Throwable $th) {
            // Registrar error para depuración
            //\Log::error('Error al eliminar la factura: ' . $th->getMessage());
            // Notificar fallo
            $this->noty('No se pudo eliminar la factura. Revisa los logs para más detalles.' . $th->getMessage());
        }
    }


    public function resetUI()
    {
       $this->resetPage();
       $this->resetValidation();
       $this->reset('search', 'motivoNC', 'selectedFacturaId');
    }




   public  function show(Factura $factura)  {

        $this->factura_detalle = $factura->load('detalles');
        //dd($this->factura_detalle);

        $this->noty('','show_factura');
    }

}
