<?php

namespace App\Models;

use App\Traits\FuncionesTrait;
use App\Traits\PdfTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Unique;
use phpseclib3\File\X509 as FileX509;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use phpseclib\File\X509;
use phpseclib\Crypt\RSA;
use nusoap_client;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Break_;
//require_once "/vendor/econea/nusoap/src/nusoap.php";
use soap_server;

class Factura extends Model
{
    use HasFactory;
    use WithFileUploads;
    use FuncionesTrait;
    use PdfTrait;
    use SoftDeletes;

    //const $PASSCERTIFICADO =  Certificado::first('pass');

    protected $dates = ['deleted_at']; // Indica que deleted_at es una fecha

    protected $fillable = ['secuencial','numeroAutorizacion','fechaAutorizacion','codDoc','claveAcceso','customer_id',
                            'user_id','subtotal','descuento','total','formaPago',
                            'factura_modificada_id', 'motivo_nc' // New Fields for NC
                            ];


    // validaciones

    public static function rules($id)
    {
        if($id < 0) // crear
        {
            return[

                'secuencial' => 'required',
                'codDoc' => 'required',
                'claveAcceso'=> 'required',
                'customer_id'=> 'required',
                'user_id'=> 'required',
                // 'subtotal'=> 'required',
                // 'subtotal0'=> 'required',
                // 'subtotal12'=> 'required',
                // 'ice'=> 'required',
                // 'iva12'=> 'required',
                // 'total'=> 'required',
                // 'formaPago'=> 'required',

            ];

        }
    }

    public static $messages =[
        'user_id.required' => 'usuario requerido',
        'customer_id.required' => 'cliente es requerido',
        'codDoc.required' => 'Código es  requerido',
        'claveAcceso.required' => 'calve de acceso  requerido'
        // 'subtotal.required' => 'subtotal  requerido',
        // 'subtotal0.required' => 'subtotal0  requerido',
        // 'subtotal12.required' => 'subtotal12  requerido',
        // 'ice.required' => 'ice  requerido',
        // 'descuento.required' => 'descuento  requerido',
        // 'iva12.required' => 'iva12  requerido',

    ];


    // empresa para sacar datos y formar la clave de acceo

    public function empresa ()
    {
        //$empresa =  Cache::get('settings');
        //$empresa = Setting::first();
        $empresa =  empresa();
        return $empresa;
    }

    public function claveAcceso($codDoc = '01')
    {
         $fecha =  Carbon::now()->format('dmY'); //1
         $codigo  = $codDoc; //2
         $parteUno = $fecha.$codigo;   //1+2***********
         $empresa =  $this->empresa();
         $ruc =  $empresa->ruc;  //3
         $ambiente  =  $empresa->ambiente;  //4
         $establecimiento =  $empresa->estab;
         $puntoEmi  =  $empresa->ptoEmi;
         $serie  = $establecimiento.$puntoEmi;  //5
         $parteDos =  $ruc.$ambiente.$serie;  // 3 4 y 5***********
         $cadenaUNo = $parteUno.$parteDos;   /// 1 al 5 *********************
         $secuencial =  $this->secuencial($codDoc); //6 (Pass codDoc to get correct sequence)
         $codigoNumerico  =substr($secuencial,-8);  // secuencial 8 desde la derecha
         $tipoEmi  = "1";   //8
         $cadenaDos  = $cadenaUNo.$secuencial.$codigoNumerico.$tipoEmi;   // 1 al 8   **********
         $dig  =  $this->getMod11Dv($cadenaDos);
         $claveFinal = $cadenaDos.$dig;
        return $claveFinal ;
       //return $cadena;
    }

    public function generaClave($param){
        $claveArray = [];
        /*
         * Generar con ceros la tabla de hasta 49 posiciones
         */
        for($x=0;$x<49;$x++) {
          $claveArray[$x] = 0;
        }
        /*
         * Proceso de convertir cada campo en array para adicionar a la array de la clave
         */

        $args['tabla'] = $param['fecha'];
        $args['posini'] = 0;
        $args['posfin'] = 7;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);
        //echo 'Pasa fecha';

        $args['tabla'] = $param['tipodoc'];
        $args['posini'] = 8;
        $args['posfin'] = 9;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);
        //echo 'Pasa tipo documento';

        $args['tabla'] = $param['ruc'];
        $args['posini'] = 10;
        $args['posfin'] = 22;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);
        //echo 'Pasa ruc';


        $args['tabla'] = $param['ambiente'];
        $args['posini'] = 23;
        $args['posfin'] = 23;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);



        $args['tabla'] = $param['establecimiento'];
        $args['posini'] = 24;
        $args['posfin'] = 26;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);



        $args['tabla'] = $param['punto'];
        $args['posini'] = 27;
        $args['posfin'] = 29;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);



        $args['tabla'] = $param['factura'];
        $args['posini'] = 30;
        $args['posfin'] = 38;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);



        $args['tabla'] = $param['codigo'];
        $args['posini'] = 39;
        $args['posfin'] = 46;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);



        $args['tabla'] = $param['emision'];
        $args['posini'] = 47;
        $args['posfin'] = 47;
        $args['claveArray'] = $claveArray;
        $claveArray = $this->haceArray($args);
        $digito = $this->poneDigito($claveArray);
        $claveArray[48] = $digito;
        return $claveArray;

    }


    public function haceArray($param)  {

            //    echo 'Viene ';
    //    var_dump($param);
        $paso = str_split($param['tabla']);

        $j = count($paso) - 1;
        $posini = $param['posini'];
        $posfin = $param['posfin'];
        $claveArray = $param['claveArray'];
        $flag = TRUE;
        while ($flag)
        {
            if($posfin >= $posini){
    //        echo 'Esto tiene ini ' . $posini . ' Esto tiene fin ' . $posfin;
            if ($j >= 0) {
                $claveArray[$posfin] = $paso[$j];
                $j--;
            }
            $posfin--;
            } else {
                $flag = FALSE;
            }
        }
        return $claveArray;

    }

    public function poneDigito($param) {
        $posfin = 47;
        $flag = TRUE;
        $j = 2;
        $suma = 0;
        while ($flag) {
            if ($posfin >= 0) {
                $suma = $suma + ($param[$posfin] * $j);
    //            echo $suma;
                $j++;
                if ($j > 7) {
                    $j = 2;
                }
                $posfin--;
            } else {
                $flag = FALSE;
            }
        }
    //    echo 'Esta es la suma ' . $suma;
        $tienecero = $suma % 11;
        if ($tienecero == 0){
            $digito = 0;
        } else {
            $digito = 11 - ($suma % 11);
        }
    //    echo '<br>Este es el digito verificador ' . $digito;
        return $digito;
    }



    public  function secuencial ($codDoc = '01')
    {
        $numeroSecuencial = 1;
        DB::transaction(function () use (&$numeroSecuencial, $codDoc){
            $ultimaFactura =  DB::table('facturas')
                ->where('codDoc', $codDoc) // Filtrar por tipo de documento
                ->lockForUpdate()
                ->orderByDesc('secuencial')
                ->first();

                $startSec = 1;
                // Only for invoices, check config starting sequence
                if ($codDoc == '01') {
                    $startSec = intval(empresa()->secuencial_factura ?? 1);
                }

                if ($ultimaFactura == null) {
                    $numeroSecuencial = $startSec;
                } else {
                    // Always use the highest between DB+1 and Config
                    $dbNext = intval($ultimaFactura->secuencial) + 1;
                    $numeroSecuencial = max($dbNext, $startSec);
                }
        });

        // Formatear el número secuencial con ceros a la izquierda y asegurarse de que no exceda 9 dígitos
        $numeroFormateado = str_pad($numeroSecuencial, 9, '0', STR_PAD_LEFT);
        $numeroFormateado = substr($numeroFormateado, -9); // Limitar a 9 dígitos

        return $numeroFormateado;
    }

    public function getMod11Dv($num)
    {

        $digits = str_replace( array( '.', ',' ), array( ''.'' ), strrev($num ) );
        if ( ! ctype_digit( $digits ) )
        {
          return false;
        }

        $sum = 0;
	    $factor = 2;
        for( $i=0;$i<strlen( $digits ); $i++ )
        {
          $sum += substr( $digits,$i,1 ) * $factor;
          if ( $factor == 7 )
          {
            $factor = 2;
          }else{
           $factor++;
         }
        }
        $dv = 11 - ($sum % 11);
        if ( $dv == 10 )
	  {
	    return 1;
	  }
	  if ( $dv == 11 )
	  {
	    return 0;
	  }
	  return $dv;
    }









    public  function detalles (){

        return $this->hasMany(DetalleFactura::class);
    }


    public  function customer (){
        return $this->belongsTo(Customer::class);
    }

    public function xmlFile()
    {
        return $this->hasOne(XmlFile::class, 'factura_id');
    }


    public function usuario(){
        return $this->belongsTo(User::class, 'user_id');
    }


    public function impuestos(){
        return $this->hasMany(FacturaImpuesto::class, 'factura_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'formaPago', 'code');
    }

    public function facturaModificada()
    {
        return $this->belongsTo(Factura::class, 'factura_modificada_id');
    }

    public function notasCredito()
    {
        return $this->hasMany(Factura::class, 'factura_modificada_id');
    }








}
