<?php

namespace App\Http\Livewire;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{

    use WithFileUploads;

    public $logo, $leyend, $printer, $selected_id, $logoPreview, $annulment_days, $enable_caja, $secuencial_factura;
    public $rimpe_type, $agente_retencion; // [NEW]
    public $cert_file, $cert_password;
    public $firma_actividades_modo = 'manual';
    public $firma_actividades_mismo_certificado = true;
    public $firma_actividades_cert_file;
    public $firma_actividades_cert_password;

    public function mount()
    {
       // $info =  Setting::first();
        //$info =  Cache::get('settings');
        //dd(Cache::get('settings'));
        $info =  empresa();
        //dd($info->razonSocial);

        if($info){
            $this->selected_id = $info->id;
            $this->razonSocial = $info->razonSocial;
            $this->nombreComercial = $info->nombreComercial;
            $this->ruc = $info->ruc;
            $this->estab = $info->estab;
            $this->ptoEmi = $info->ptoEmi;
            $this->dirMatriz = $info->dirMatriz;
            $this->dirEstablecimiento = $info->dirEstablecimiento;
            $this->telefono = $info->telefono;
            $this->email = $info->email;
            $this->ambiente = $info->ambiente;
            $this->tipoEmision = $info->tipoEmision;
            $this->contribuyenteEspecial = $info->contribuyenteEspecial;
            $this->obligadoContabilidad = $info->obligadoContabilidad;
            $this->logoPreview = $info->logo;
            $this->leyend = $info->leyend;
            $this->printer = $info->printer;
            $this->annulment_days = $info->annulment_days;
            $this->cert_file = $info->cert_file;
            $this->cert_password = $info->cert_password;
            $this->enable_caja = $info->enable_caja;
            $this->secuencial_factura = $info->secuencial_factura;
            $this->rimpe_type = $info->rimpe_type; // [NEW]
            $this->agente_retencion = $info->agente_retencion; // [NEW]
            $this->firma_actividades_modo = $info->firma_actividades_modo ?: 'manual';
            $this->firma_actividades_mismo_certificado = $info->firma_actividades_mismo_certificado === null
                ? true
                : (bool) $info->firma_actividades_mismo_certificado;
            $this->firma_actividades_cert_file = $info->firma_actividades_cert_file;
            $this->firma_actividades_cert_password = null;
        }
    }

    public function render()
    {
        return view('livewire.settings.component')
        ->layout('layouts.theme.app');
    }

    public function updatedFirmaActividadesCertFile(): void
    {
        if ($this->firma_actividades_cert_file && !is_string($this->firma_actividades_cert_file)) {
            $this->validateOnly('firma_actividades_cert_file', [
                'firma_actividades_cert_file' => 'file|mimes:p12,pfx|max:2048',
            ], [
                'firma_actividades_cert_file.mimes' => 'El archivo debe ser .p12 o .pfx.',
                'firma_actividades_cert_file.max' => 'El archivo no debe superar 2 MB.',
            ]);
        }
    }

    public function updatedCertFile() {
        Log::info('Hook updatedCertFile triggered');
        if($this->cert_file) {
            Log::info('File content type: ' . gettype($this->cert_file));
            try {
                 Log::info('Temp filename: ' . $this->cert_file->getFilename());
            } catch(\Exception $e) {
                 Log::error('Error accessing file in hook: ' . $e->getMessage());
            }
        } else {
             Log::info('File is null in hook');
        }
    }


    public function Store()
    {
        Log::info('Settings Store Called');
        Log::info('Cert File Type: ' . gettype($this->cert_file));
        if (is_object($this->cert_file)) {
             Log::info('Cert File Class: ' . get_class($this->cert_file));
             Log::info('Cert File Name: ' . $this->cert_file->getClientOriginalName());
        } else {
             Log::info('Cert File Value: ' . $this->cert_file);
        }

        $rules = [
            'razonSocial' => 'required',
            'nombreComercial' => 'required',
            'ruc' => 'required|max:13',
            'estab' => 'required|max:3',
            'ptoEmi' => 'required|max:3',
            'dirMatriz' => 'required',
            'dirEstablecimiento' => 'required',
            'telefono' => 'required',
            'email' => "required|email|unique:settings,email," . ($this->selected_id ?? 'NULL'), // Fix unique validation for new record
            'ambiente' => 'required|max:1',
            'tipoEmision' => 'required|max:1',
            'contribuyenteEspecial' => 'required|max:13',
            'obligadoContabilidad' => 'required|max:2',
            'cert_file' => 'nullable', // Add basic rule to allow file processing
            'rimpe_type' => 'required', // [NEW]
            'agente_retencion' => 'nullable|max:20', // [NEW]
            'firma_actividades_modo' => 'required|in:manual,predeterminado,automatico',
            'firma_actividades_mismo_certificado' => 'boolean',
            'firma_actividades_cert_file' => 'nullable',
            'firma_actividades_cert_password' => 'nullable|string',

        ];

        $messages =[
            'razonSocial.required' => 'Ingrese la razón social de la empresa',
            'nombreComercial.required' => 'Ingrese el nombre comercial de la empresa',
            'estab.required' => 'Ingrese el código del establecimiento',
            'estab.max' => 'Código del establecimiento debe ser máximo 3  caracteres',
            'ruc.required' => 'Ingrese un ruc ',
            'ruc.max' => 'Ruc debe tener máximo 13 caracteres ',
            'ptoEmi.required' => 'Ingrese un punto de emisión ',
            'ptoEmi.max' => 'Punto emision  debe tener máximo 3 caracteres ',
            'dirMatriz.required' => 'Ingrese la direccion matriz',
            'dirEstablecimiento.required' => 'Ingrese la direccion de establecimiento',
            'telefono.required' => 'Ingrese el teléfono del establecimiento',
            'email.required' => 'Ingrese el correo ',
            'email.email' => 'Ingrese un correo válido',
            'ambiente.required' => 'Ingrese  el ambiente del sistema',
            'ambiente.max' => 'El ambiente debe ser de un solo caracter',
            'tipoEmision.required' => 'Ingrese  el tipo de emision',
            'tipoEmision.max' => 'El tipo de emisión debe ser de un solo caracter',
            'contribuyenteEspecial.required' => 'Ingrese si es contribuyente especial',
            'contribuyenteEspecial.max' => 'El codigo contribuyente especial debe tener máximo 13 caracteres',
            'obligadoContabilidad.required' => 'Campo requerido',
            'obligadoContabilidad.max' => 'El campo debe tener máximo 2 caracteres',
            'firma_actividades_modo.required' => 'Seleccione la política de firma de actividades.',
            'firma_actividades_modo.in' => 'La política de firma seleccionada no es válida.',


        ];

        $this->validate($rules, $messages);
   
        // Obtener configuración existente
        $config = Setting::first();

        $nuevoCertificadoActividades = $this->firma_actividades_cert_file
            && !is_string($this->firma_actividades_cert_file);
        $archivoActividadesActual = $config->firma_actividades_cert_file ?? null;
        $passwordActividadesActual = $config->firma_actividades_cert_password ?? null;

        if (!$this->firma_actividades_mismo_certificado) {
            if (!$nuevoCertificadoActividades && !$archivoActividadesActual) {
                $this->addError('firma_actividades_cert_file', 'Seleccione el certificado para firmar actividades.');
            }
            if ($nuevoCertificadoActividades && empty($this->firma_actividades_cert_password)) {
                $this->addError('firma_actividades_cert_password', 'Ingrese la contraseña del nuevo certificado.');
            } elseif (empty($this->firma_actividades_cert_password) && !$passwordActividadesActual) {
                $this->addError('firma_actividades_cert_password', 'Ingrese la contraseña del certificado para firmar actividades.');
            }

            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }
        }
        
        // Manejo de Certificado
        $certFileName = null;
        
        // Solo procesar si hay un archivo subido (objeto)
        if($this->cert_file && !is_string($this->cert_file)){
            $certFileName =  $this->cert_file->getClientOriginalName();
            $this->cert_file->storeAs('', $certFileName, 'certificados');
        } else {
            // Si no se subió nuevo, mantener el existente
            $certFileName = $config->cert_file ?? null;
            // Si el usuario quiere borrarlo? (No implementado aun, asumimos que si no toca input, mantiene)
            // Si $this->cert_file es string (del mount), es el valor viejo.
            if (is_string($this->cert_file)) {
                 $certFileName = $this->cert_file;
            }
        }

        $firmaCertFileName = $archivoActividadesActual;
        if ($nuevoCertificadoActividades) {
            $firmaCertFileName = 'firma_actividades_' . $this->firma_actividades_cert_file->getClientOriginalName();
            $this->firma_actividades_cert_file->storeAs('', $firmaCertFileName, 'certificados');
        } elseif (is_string($this->firma_actividades_cert_file)) {
            $firmaCertFileName = $this->firma_actividades_cert_file;
        }

        $firmaCertPassword = !empty($this->firma_actividades_cert_password)
            ? $this->firma_actividades_cert_password
            : $passwordActividadesActual;

        $data = [
            'razonSocial' => $this->razonSocial,
            'nombreComercial' => $this->nombreComercial,
            'ruc' => $this->ruc,
            'estab' => $this->estab,
            'ptoEmi' => $this->ptoEmi,
            'dirMatriz' => $this->dirMatriz,
            'dirEstablecimiento' => $this->dirEstablecimiento,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'ambiente' => $this->ambiente,
            'tipoEmision' => $this->tipoEmision,
            'contribuyenteEspecial' => $this->contribuyenteEspecial,
            'obligadoContabilidad' => $this->obligadoContabilidad,
            'cert_file' => $certFileName,
            'cert_password' =>  !empty($this->cert_password) ? $this->cert_password : ($config->cert_password ?? null),
            'leyend' => $this->leyend,
            'printer' => $this->printer,
            'annulment_days' => $this->annulment_days,
            'enable_caja' => $this->enable_caja ? 1 : 0,
            'secuencial_factura' => $this->secuencial_factura,
            'rimpe_type' => $this->rimpe_type, // [NEW]
            'agente_retencion' => $this->agente_retencion, // [NEW]
            'firma_actividades_modo' => $this->firma_actividades_modo,
            'firma_actividades_mismo_certificado' => $this->firma_actividades_mismo_certificado ? 1 : 0,
            'firma_actividades_cert_file' => $firmaCertFileName,
            'firma_actividades_cert_password' => $firmaCertPassword,
        ];

        if ($config) {
            $config->update($data);
            $this->selected_id = $config->id; // Maintain ID
        } else {
            $config = Setting::create($data);
            $this->selected_id = $config->id; 
        }

        $this->cert_file = $certFileName;
        $this->firma_actividades_cert_file = $firmaCertFileName;

        // Manejo de Logo
        if ($this->logo != null && !is_string($this->logo)) { // Ensure it is an upload
             $tempLogo = $config->logo;
             
            //eliminar logo anterior si existe y no es el default
            if ($tempLogo && File::exists(public_path($tempLogo))) {
                File::delete(public_path($tempLogo));
            }

            // guardar logo en la db
            $customFileName = uniqid() . '_.' . $this->logo->extension();
            $config->logo = $customFileName;
            $config->save();

            // storage logo
            $this->logo->storeAs('', $customFileName, 'public2'); 

            $this->logoPreview = $customFileName;
            $this->logo = null;
        }

        $this->dispatchBrowserEvent('noty', ['msg'=> 'CONFIGURACIÓN GUARDADA CORRECTAMENTE', 'type' => 'success']);

    }
}
