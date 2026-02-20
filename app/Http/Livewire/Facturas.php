<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\FacturaImpuesto;
use App\Models\Product;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\XmlFactura;
use Livewire\Component;
use App\Traits\CartTrait;
use App\Traits\PrinterTrait;
use App\Traits\PdfTrait;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log; // Added Log
use App\Services\XmlGeneratorService; // Added Service
use App\Services\SignatureService; // Added Service
use App\Services\SriSoapService; // Added Service
use App\Jobs\SendInvoiceEmail;
use App\Http\Controllers\PdfController;
//use Codedge\Fpdf\Facades\Fpdf;
use Codedge\Fpdf\Fpdf\Fpdf;
//use Barryvdh\DomPDF\Facade as PDF;
//use Barryvdh\DomPDF\PDF as PDF;
use PhpParser\Node\Stmt\Return_;

class Facturas extends Component
{

    protected $listeners = ['cancelSale'];

     //traits
     use CartTrait, PrinterTrait, PdfTrait;

     // propiedades generales
    public  $cash, $searchCustomer, $searchProduct, $customer_id =null,
     $changes,  $customerSelected ="Seleccionar Cliente", $productSelected = "Buscar producto",$productNameSelected="",$productChangesSelected="",
     $claveAcceso ='', $secuencial ='', $fechaFactura, $paymentMethods = [], $selectedPaymentMethod = '01';

    // Registro rápido de cliente
    public $showCreateCustomer = false;
    public $q_businame, $q_valueidenti, $q_typeidenti = 'cedula', $q_address, $q_phone, $q_email;

    // mostrar y activar panels
    public $showListProducts = false, $tabProducts =  true, $tabCategories = false;

     //collections
     public $productsList =[], $customers =[], $products = [];

     //info del carrito
    public $totalCart = 0, $itemsCart= 0, $contentCart=[];

    //totales
    public $subTotSinImpuesto =0;
    public $subtotalSinImpuestos = 0; // Total de productos sin impuestos


     // producto seleccionado
     public $productIdSelected;

     // impuestos
     public  $totalDscto=0;

     //dinamicos para la tabla
    // public $subtotal0 = 0, $subtotal15 = 0, $totalImpuesto15 =0;
      public  $impuestos = [];  // Ejemplo: ['IVA 15' => 5.00, 'ICE 3101' => 2.00]
     public $subtotales = []; // Ejemplo: ['IVA 15' => 30.00, 'ICE 3101' => 10.00]

     protected $paginationTheme = "bootstrap";

     public $estadoCaja;




     // carga al inicio

     public function mount()
     {

        $fact  = new Factura();
        $this->claveAcceso = $fact->claveAcceso();
        $this->secuencial = $fact->secuencial();
        $this->validaCaja();
        $this->paymentMethods = PaymentMethod::active();
        //$this->recalcularTotales();
     }



    public function render()
    {


        $this->fechaFactura =  Carbon::now()->format('d-m-Y');

      // dd($this->secuencial , $this->claveAcceso);
      // dd($this->claveAcceso);
        // $this->validaCaja(); // Moved to mount for performance
        if(strlen($this->searchCustomer) > 0)
            $this->customers =  Customer::where('businame','like',"%{$this->searchCustomer}%")
             ->orderBy('businame','asc')->limit(5)->get(); // Optimizado: SQL Limit
        else
            $this->customers =  Customer::orderBy('businame','asc')->limit(5)->get(); // Optimizado: SQL Limit
            //$this->totalCart = $this->getTotalCart();
            $this->itemsCart = $this->getItemsCart();
           // $this->subTotSinImpuesto =  $this->getTotalSICart();
            $this->contentCart = $this->getContentCart();
            $this->recalcularTotales();

            if(strlen($this->searchProduct) > 0) {
                $this->products = Product::where('name', 'like', "%{$this->searchProduct}%")
                    ->where(function ($query){
                        $query->where('stock','>',0)
                            ->orWhere('es_servicio', true);
                    })->orderBy('created_at', 'desc') // Ordena por fecha de creación para mostrar los más recientes primero
                    ->limit(5) // Trae solo los primeros 5 resultados
                    ->get();
            } else {
                $this->products = Product::where(function ($query){
                    $query->where('stock','>',0)
                        ->orWhere('es_servicio', true);
                        })->orderBy('created_at', 'desc') // Ordena los más recientes primero
                        ->limit(5) // Trae solo los primeros 5
                        ->get();
            }

            //dd($this->products);

        return view('livewire.facturas.component', [
            'categories' => $this->tabCategories ? Category::orderBy('name','asc')->get() : [],
        ])
        ->layout('layouts.theme.app');

    }



    public function  validaCaja()
    {

        $settings = \App\Models\Setting::first();
        if ($settings && !$settings->enable_caja) {
            $this->estadoCaja = 'abierta'; // Estado abierto simulado para permitir facturación
            return;
        }

        $user_id  =  Auth()->user()->id;
        $usuario = User::find($user_id);
        $this->estadoCaja = $usuario->caja->status ?? 'nocajasasignadas' ;

    }

    public function setTabActive($tabName)
    {
        if ($tabName == 'tabProducts') {
            $this->tabProducts = true;
            $this->tabCategories = false;
        }
        else
        {
            $this->tabProducts = false;
            $this->showListProducts = false;
            $this->tabCategories = true;
        }
    }

    public function noty($msg, $eventName= 'noty', )
    {
        $this->dispatchBrowserEvent($eventName, ['msg' => $msg, 'type' => 'success']);
    }

    //operaciones con el carrito
    public function getProductsByCategory($category_id)
    {
        $this->showListProducts =  true;
        $this->productsList = Product::where('category_id', $category_id)
                        ->where(function ($query) {
                            $query->where('stock', '>', 0)
                                ->orWhere('es_servicio', true);
                        })
                        ->get();
     }

    public function add2Cart(Product $product)
    {

       $this->addProductCart($product, 1, $this->changes);
       $this->changes = '';
       $this->recalcularTotales();
       //$this->subTotSinImpuesto = $this->subTotSinImpuesto + $product->price;
       //dd($this->subTotSinImpuesto);
    }

    public function increaseQty(Product $product, $cant=1)
    {
        $this->updateQtyCart($product, $cant);
        $this->recalcularTotales();
    }

    public function decreaseQty($productId)
    {
        $this->decreaseQtyCart($productId);
        $this->recalcularTotales();
    }

    public function removeFromCart($id)
    {
        $this->removeProductCart($id);
        $this->recalcularTotales();
    }

    public function updateQty(Product $product, $cant=1)
    {
        //para validar si hay las suficientes existencias en bbd y poder vender
        if($cant  + $this->countInCart($product->id) > $product->stock){
            $this->noty('STOCK INSUFICIENTE','noty','error');
            return;
        }
        if ($cant <= 0){

            $this->removeProductCart($product->id);
            $this->recalcularTotales();
        }
        else{
            $this->replaceQuantityCart($product->id, $cant);
            $this->recalcularTotales();
        }

    }

      // para los cambios en el modal
      public function removeChanges()
      {
          $this->clearChanges($this->productIdSelected);
          $this->dispatchBrowserEvent('close-modal-changes'); // evento que va al front para cerrar el modal (a traves de JS)

      }

      public function addChanges($changes)
      {
          $this->addChanges2Product($this->productIdSelected, $changes);
          $this->dispatchBrowserEvent('close-modal-changes');
      }

      // Optimización: Selección directa por ID
      public function selectCustomer($id, $name)
      {
          $this->customer_id = $id;
          $this->customerSelected = $name;
          $this->dispatchBrowserEvent('close-customer-modal');
      }

      public function updatedCustomerSelected()
      {
          $this->dispatchBrowserEvent('close-customer-modal');
      }

      public function showCreateCustomer()
      {
          $this->showCreateCustomer = true;
          $this->resetQuickCustomer();
          if($this->searchCustomer && is_numeric($this->searchCustomer)) {
              $this->q_valueidenti = $this->searchCustomer;
          } elseif($this->searchCustomer) {
              $this->q_businame = $this->searchCustomer;
          }
      }

      public function hideCreateCustomer()
      {
          $this->showCreateCustomer = false;
      }

      public function resetQuickCustomer()
      {
          $this->reset('q_businame', 'q_valueidenti', 'q_typeidenti', 'q_address', 'q_phone', 'q_email');
          $this->q_typeidenti = 'cedula';
      }

      public function saveQuickCustomer()
      {
          $this->validate([
              'q_businame' => 'required|min:3',
              'q_valueidenti' => 'required|unique:customers,valueidenti',
              'q_typeidenti' => 'required',
              'q_email' => 'nullable|email|unique:customers,email'
          ], [
              'q_businame.required' => 'El nombre es requerido',
              'q_valueidenti.required' => 'La identificación es requerida',
              'q_valueidenti.unique' => 'La identificación ya está registrada',
              'q_email.email' => 'Correo inválido',
              'q_email.unique' => 'El correo ya está registrado'
          ]);

          try {
              $customer = Customer::create([
                  'businame' => $this->q_businame,
                  'valueidenti' => $this->q_valueidenti,
                  'typeidenti' => $this->q_typeidenti,
                  'address' => $this->q_address ?? 'S/N',
                  'phone' => $this->q_phone ?? '0000000000',
                  'email' => $this->q_email ??  $this->q_valueidenti . '@facta.ec', // Usar ID para evitar duplicados si no hay mail
                  'notes' => 'Registro rápido desde Facturación'
              ]);

              if($customer) {
                  $this->customer_id = $customer->id;
                  $this->customerSelected = $customer->businame;
                  $this->noty('Cliente registrado y seleccionado');
                  $this->dispatchBrowserEvent('close-customer-modal');
                  $this->showCreateCustomer = false;
                  $this->resetQuickCustomer();
                  $this->searchCustomer = '';
              }
          } catch (\Exception $e) {
              \Log::error("[QUICK_CUSTOMER_ERROR] " . $e->getMessage());
              $this->noty("Error al guardar: " . $e->getMessage(), 'noty', 'error');
          }
      }

      public function searchManualProduct(Product $product)
      {
          //dd($id);
          $this->add2Cart($product);
          $this->dispatchBrowserEvent('close-product-modal');
          //$this->resetUI();

      }

      public function resetUI()
      {
        // No resetear 'secuencial' ni 'claveAcceso' aquí directamente si los vamos a regenerar abajo.
        // O mejor, los reseteamos y los regeneramos inmediatamente.
        $this->reset('cash','searchCustomer','searchProduct','customer_id','changes','customerSelected','productSelected','fechaFactura',
        'showListProducts','tabProducts','tabCategories','productsList','customers','products','totalCart','itemsCart','contentCart',
        'subTotSinImpuesto','subtotalSinImpuestos','productIdSelected','totalDscto','impuestos','subtotales');
        
        // Regenerar secuencial y clave de acceso para la NUEVA venta
        $fact  = new Factura();
        $this->claveAcceso = $fact->claveAcceso();
        $this->secuencial = $fact->secuencial();

        $this->tabProducts = true;
        $this->tabCategories = false;
        $this->itemsCart = 0;
        $this->contentCart = collect();
        $this->selectedPaymentMethod = '01';
        $this->recalcularTotales();

      }


      public function recalcularTotales()
      {
          $this->subTotSinImpuesto = 0;  // Subtotal sin impuestos
          $this->totalDscto = 0;         // Total descuento
          //$this->totalCart = 0;          // Total general del carrito

          // Array dinámico para impuestos y subtotales por tipo de impuesto
          $this->impuestos = [];  // Ejemplo: ['IVA 15' => 5.00, 'ICE 3101' => 2.00]
          $this->subtotales = []; // Ejemplo: ['IVA 15' => 30.00, 'ICE 3101' => 10.00]

          $this->subtotalSinImpuestos = 0; // Total de productos sin impuestos


          // Iterar los productos del carrito
          foreach ($this->contentCart as &$producto) { // Referencia con & para modificar directamente
              $subtotalProducto = $producto['price'] * $producto['qty'];
              $this->subTotSinImpuesto += $subtotalProducto;

              // Calcular descuento si existe (como porcentaje)
              $montoDescuento = isset($producto['descuento']) ? $subtotalProducto * ($producto['descuento'] / 100) : 0;
              $this->totalDscto += $montoDescuento;

              // Base imponible después de descuento
              $baseImponible = $subtotalProducto - $montoDescuento;

              // Inicializar el total de impuestos para este producto
              $producto['total_impuesto'] = 0;

              // Si el producto no tiene impuestos, acumular en subtotal sin impuestos
              if (empty($producto['impuestos'])) {
                  $this->subtotalSinImpuestos += $baseImponible;
              }

              // Iterar sobre los impuestos asignados al producto
              foreach ($producto['impuestos'] as $tax) {
                  $nombreImpuesto = $tax['nombre'] ; // Ejemplo: IVA 15 o ICE 3101
                  $porcentaje = $tax['porcentaje'];
                  $montoImpuesto = round($baseImponible * $porcentaje / 100, 2);

                  // Acumular montos de impuestos
                  $this->impuestos[$nombreImpuesto] = ($this->impuestos[$nombreImpuesto] ?? 0) + $montoImpuesto;

                  // Acumular base imponible por tipo de impuesto
                  $this->subtotales[$nombreImpuesto] = ($this->subtotales[$nombreImpuesto] ?? 0) + $baseImponible;




                  // Sumar al total de impuestos del producto

                  $producto['total_impuesto'] += $montoImpuesto;
              }
          }

          // Calcular el total del carrito: subtotal - descuentos + impuestos totales
          $this->totalCart = round(
              $this->subTotSinImpuesto - $this->totalDscto + array_sum($this->impuestos),
              3
          );

      }


      // guardar venta
      public function storeSale($print = false)
      {
        Log::info('Inicio storeSale en Facturas', ['tenant' => tenant('id'), 'user' => Auth::id()]);

        if ($this->getItemsCart() <= 0) {
            $this->noty('AGREGA PRODUCTOS A LA VENTA', 'noty', 'error');
            $this->dispatchBrowserEvent('sale-error'); // Close spinner
            return;
        }

        // VALIDACIÓN DE LÍMITE POR PLAN
        $tenant = tenant();
        $limit = $tenant->getInvoiceLimit();
        // Nota: getCurrentCycleInvoiceCount() ya filtra solo las facturas AUTORIZADAS (numeroAutorizacion != null).
        // Por lo tanto, se bloquea la emisión si ya ha alcanzado su cupo de facturas válidas.
        if ($limit !== null && $tenant->getCurrentCycleInvoiceCount() >= $limit) {
            $this->noty("Has alcanzado el límite de facturas de tu plan contratado ({$limit}). Realiza un nuevo pago para renovar.", 'noty', 'error');
            $this->dispatchBrowserEvent('sale-error');
            return;
        }

        DB::beginTransaction();
        try
        {
            if ($this->customerSelected !=  'Seleccionar Cliente') {
                $customer = Customer::where('businame', $this->customerSelected)->first();
            } else{
                $customer = Customer::where('businame', 'consumidor final')->first();
                // Fallback flexible
                if (!$customer) {
                     $customer = Customer::where('businame', 'like', 'Consumidor Final')->first();
                }
            }

            if (!$customer) {
                Log::error('Cliente no encontrado en storeSale');
                $this->noty('Error: No se encontró el cliente (Consumidor Final). Ejecute demo:reset', 'noty', 'error');
                $this->dispatchBrowserEvent('sale-error');
                DB::rollback();
                return;
            }

            $this->customer_id = $customer->id;

            // VALIDACIÓN SRI: Límite $50 Consumidor Final (Manual v2.32, Sección 9.10)
            // "Si el valor de la factura es mayor a 50 USD se deberá especificar obligatoriamente los datos del adquirente."
            if ($customer->valueidenti === '9999999999999' && $this->totalCart > 50) {
                 $this->noty('BLOQUEADO: Ventas a Consumidor Final no pueden superar los $50 USD. Ingrese los datos del cliente (Normativa SRI).', 'noty', 'error');
                 $this->dispatchBrowserEvent('sale-error');
                 DB::rollback();
                 return;
            }

            //$customer =  Customer::find($this->customer_id); // Redundant found above
            $tipo = $customer->typeidenti;
            $tipeIDenti = ($tipo == 'ruc') ? '04' : '05'; // default to cedula

            $factura  =  Factura::create([
                'secuencial' => $this->secuencial,
                'codDoc' => '01',
                'claveAcceso' =>   $this->claveAcceso,
                'customer_id' =>  $this->customer_id,
                'user_id' => Auth()->user()->id,
                'subtotal' => $this->subTotSinImpuesto,
                'descuento' => $this->totalDscto,
                // 'descuento' => $this->totalDscto, // Removed duplicate
                'total' => $this->totalCart,
                'formaPago' => $this->selectedPaymentMethod
            ]);

            Log::info('Factura creada en DB', ['id' => $factura->id]);

            if ($factura) {
                $items =  $this->getContentCart();
                
                 // Optimización N+1: Cargar todos los productos de una vez
                 $productIds = collect($items)->pluck('id');
                 $productsToUpdate = Product::whereIn('id', $productIds)->get()->keyBy('id');

                foreach($items  as $item)
                {
                    DetalleFactura::create([
                        'factura_id' => $factura->id,
                        'product_id' => $item->id,
                        'cantidad' => $item->qty,
                        'descripcion' => $item->name,
                        'precioUnitario' => $item->price,
                        'descuento' =>$item->descuento,
                        'total' =>$item->price * $item->qty
                    ]);

                     // Actualizar stock
                     if(isset($productsToUpdate[$item->id])) {
                         $product = $productsToUpdate[$item->id];
                         if ($product->es_servicio == 0){
                            $product->stock = $product->stock - $item->qty;
                            $product->save();
                         }
                     }

                     foreach($item['impuestos'] as $tax)
                     {
                        FacturaImpuesto::create([
                            'factura_id' => $factura->id,
                            'nombre_impuesto' => $tax['nombre'],
                            'codigo_impuesto' => $tax['codigo'],
                            'codigo_porcentaje' => $tax['codigo_porcentaje'],
                            'base_imponible' => ($item['price'] * $item['qty']) * (1 - ($item['descuento'] ?? 0) / 100),
                            'valor_impuesto' => round((($item['price'] * $item['qty']) * (1 - ($item['descuento'] ?? 0) / 100)) * ($tax['porcentaje'] / 100), 2),
                     ]);
                     }
                }
            }

            DB::commit();
            Log::info('Transacción DB commiteada');

            // PROCESO DE FACTURACIÓN ELECTRÓNICA
            try {
                // SALVAGUARDA ELIMINADA: EL MODO DEMO YA NO EXISTE

                    $xmlService = app(XmlGeneratorService::class);
                    $signatureService = app(SignatureService::class);
                    $sriService = app(SriSoapService::class);

                    // 1. Generar XML
                    $xmlFile = $xmlService->generate(
                        $factura->id,
                        $tipeIDenti,
                        $customer->businame,
                        $customer->valueidenti,
                        $customer->address,
                        $this->subTotSinImpuesto,
                        $this->totalDscto,
                        $this->totalCart,
                        $this->getContentCart(),
                        $this->secuencial,
                        $this->claveAcceso,
                        $factura->impuestos->toArray(), 
                        $factura->empresa(),
                        $factura->created_at->format('d/m/Y'),
                        $factura->formaPago
                    );

                    // 2. Firmar XML
                    $signedXmlObj = $signatureService->firmarFactura($xmlFile, $factura->id, $factura->empresa());

                    // 3. Enviar al SRI
                    $sriService->enviarAlSri($signedXmlObj, $factura->id);

                    // 4. Autorizar (Se puede agregar un pequeño delay si es necesario)
                    // sleep(1); 
                    $authResponse = $sriService->consultarAutorizacion($signedXmlObj, $factura->id);

                    if (isset($authResponse['estado']) && $authResponse['estado'] == 'AUTORIZADO') {
                        $factura->fechaAutorizacion = Carbon::now(); 
                        $factura->numeroAutorizacion = $authResponse['numeroAutorizacion'];
                        $factura->save();
                        
                        // Enviar correo
                        try {
                            $pdfController = new PdfController();
                            $pdfController->generatePdf($factura, false);
                            SendInvoiceEmail::dispatchSync($factura);
                        } catch (\Exception $e) {
                            Log::error('Error enviando correo factura: ' . $e->getMessage());
                        }

                        $this->noty('FACTURA AUTORIZADA POR EL SRI');
                    } else {
                        $estado = $authResponse['estado'] ?? 'DESCONOCIDO';
                        $this->noty('Factura enviada pero NO AUTORIZADA aún. Estado: ' . $estado, 'noty', 'warning');
                    }

            } catch (\Exception $e) {
                // Loguear error pero no revertir la venta, ya que ya se cobró. 
                Log::error('Error en SRI/Demo Logic: ' . $e->getMessage());
                $this->noty('Error en facturación electrónica: '. $e->getMessage(), 'noty', 'error');
                $this->dispatchBrowserEvent('sale-error');
            }

        }
        catch (\Throwable $e) {
            DB::rollback();
            Log::error('Error fatal en storeSale: ' . $e->getMessage());
            $this->noty('Error al guardar la venta: ' . $e->getMessage(), 'noty', 'error');
            $this->dispatchBrowserEvent('sale-error');
            return;
        }

        $this->clearCart();
        $this->resetUI();
        
        // Si la factura fue autorizada, abrir PDF en nueva pestaña
        try {
            if (isset($authResponse['estado']) && $authResponse['estado'] == 'AUTORIZADO') {
                 $url =  route('descargar-pdf', ['factura'=>$factura->id]);
                 $this->dispatchBrowserEvent('open-pdf', ['url' => $url]);
            } else {
                 $this->noty('Venta registrada. Factura pendiente de autorización.', 'noty', 'info');
                 $this->dispatchBrowserEvent('sale-error'); // Hide spinner
            }
        } catch (\Exception $e) {
             Log::error('Error generando URL de PDF: ' . $e->getMessage());
             $this->noty('Venta registrada, pero error al abrir PDF: ' . $e->getMessage(), 'noty', 'warning');
             $this->dispatchBrowserEvent('sale-error');
        }

        $this->emit('refreshReprocesarCount');
      }


      public function cancelSale()
      {
        $this->clearCart();
        $this->resetUI();
        $this->noty('VENTA CANCELADA');
      }
      
      public function handleError($th) // Helper if needed, but sticking to inline for now
      {
          $this->noty('Error: ' . $th->getMessage(), 'noty', 'error');
           $this->dispatchBrowserEvent('sale-error');
      }



}
