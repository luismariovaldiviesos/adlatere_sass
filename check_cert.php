<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Storage;

// This script is to check the parsed certificate data
$certPath = 'storage/app/certificados/P0000157101.p12'; 
$pass = 'Facta2024*'; // From previous logs

if (!file_exists($certPath)) {
    echo "Cert not found at $certPath\n";
    exit;
}

$pkcs12 = file_get_contents($certPath);
$certs = [];
if (openssl_pkcs12_read($pkcs12, $certs, $pass)) {
    $certData = openssl_x509_parse($certs['cert']);
    echo "Issuer Array:\n";
    print_r($certData['issuer']);
    
    // Test the logic I used
    $issuerArr = [];
    $issuerData = array_reverse($certData['issuer']);
    foreach ($issuerData as $k => $v) {
        if (is_array($v)) $v = $v[0]; 
        $issuerArr[] = "$k=$v";
    }
    $issuerName = implode(',', $issuerArr);
    echo "\nGenerated IssuerName (Reversed): $issuerName\n";
    
    $issuerArrRaw = [];
    foreach ($certData['issuer'] as $k => $v) {
        if (is_array($v)) $v = $v[0]; 
        $issuerArrRaw[] = "$k=$v";
    }
    $issuerNameRaw = implode(',', $issuerArrRaw);
    echo "Generated IssuerName (Standard): $issuerNameRaw\n";
    
    echo "\nSerial Number: " . $certData['serialNumber'] . "\n";
    echo "Serial Number (Hex): " . $certData['serialNumberHex'] . "\n";
} else {
    echo "Failed to read P12\n";
}
