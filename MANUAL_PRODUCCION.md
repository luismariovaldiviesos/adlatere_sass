# Manual de puesta en producción — Adlatere SaaS

Este manual resume la arquitectura, los pasos de despliegue y las correcciones aplicadas para que Adlatere SaaS funcione en producción con dominio central y tenants.

## 1. Arquitectura

```text
Navegador
  ↓ HTTPS
Nginx
  ↓
PHP-FPM de CloudPanel (/run/clp-php-fpm.sock)
  ↓
Laravel
  ├── Base central: adlatere
  └── Base por tenant: tenant<subdominio>
```

Dominio central:

```text
adlatere.facta.ec
```

Dominio de tenant:

```text
<subdominio>.adlatere.facta.ec
```

## 2. Requisitos previos

- DNS del dominio central apuntando al servidor.
- DNS wildcard para los tenants.
- Certificado SSL wildcard `*.adlatere.facta.ec`.
- MySQL con permisos para crear bases `tenant...`.
- Nginx con bloque para el dominio central y wildcard.
- PHP-FPM funcionando.
- Carpeta del proyecto en `/home/adminfacta/htdocs/adlatere`.

## 3. Despliegue del código

```bash
cd /home/adminfacta/htdocs/adlatere

git pull
composer install --no-dev --optimize-autoloader
composer dump-autoload -o
php artisan optimize:clear
```

Archivos importantes que deben existir:

```text
app/Services/TenantProvisioner.php
routes/tenant.php
config/tenancy.php
database/seeders/DatabaseSeeder.php
database/seeders/PlanUnicoSeeder.php
database/seeders/AdminUserSeeder.php
```

Verificar corrección de namespace:

```bash
grep -R "App\\\\http" app routes database config
```

No debe aparecer. Debe ser `App\Http`.

## 4. Variables de entorno

Archivo:

```bash
nano .env
```

Valores mínimos:

```env
APP_NAME=Adlatere
APP_ENV=production
APP_DEBUG=false
APP_URL=https://adlatere.facta.ec

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adlatere
DB_USERNAME=adlatere_user
DB_PASSWORD=CLAVE_SEGURA

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

SEED_CENTRAL_ADMIN_EMAIL=admin@adlatere.ec
SEED_CENTRAL_ADMIN_PASSWORD=CLAVE_SUPER_ADMIN
SEED_CENTRAL_ADMIN_NAME="Super Admin"

SEED_ADMIN_EMAIL=admin@mail.com
SEED_ADMIN_PASSWORD=CLAVE_ADMIN_TENANT

SAAS_SEED_TEST_PLAN=false
```

No usar `APP_DEBUG=true` en producción.

## 5. Configuración de tenancy

Archivo:

```bash
nano config/tenancy.php
```

Debe incluir el dominio central:

```php
'central_domains' => [
    '127.0.0.1',
    'localhost',
    'adlatere_saas.test',
    'adlatere.facta.ec',
],
```

Después de cambiar configuración:

```bash
php artisan optimize:clear
php artisan config:cache
```

## 6. Migraciones de la base central

Nunca usar `migrate:refresh` en producción.

```bash
php artisan migrate --force
```

Comprobar que `tarea_historiales` no esté en migraciones centrales:

```bash
ls database/migrations | grep tarea_historiales
```

No debe existir ahí. Debe estar en:

```text
database/tenant/2026_10_21_152043_create_tarea_historiales_table.php
```

Si existe la copia central, eliminarla:

```bash
rm database/migrations/2026_10_21_152043_create_tarea_historiales_table.php
```

Luego:

```bash
php artisan optimize:clear
php artisan migrate --force
```

## 7. Seeder central, planes y super admin

Ejecutar:

```bash
php artisan db:seed --class=DatabaseSeeder --force
```

Esto ejecuta:

- `PlanUnicoSeeder`
- creación/actualización del super admin central

Credenciales por defecto:

```text
Email:    admin@adlatere.ec
Password: AdlatereAdmin2026!
```

O los valores definidos en `.env`.

## 8. Plan de prueba para registro público

El plan gratuito de prueba no se crea por defecto.

Para habilitarlo:

```bash
SAAS_SEED_TEST_PLAN=true php artisan db:seed --class=PlanUnicoSeeder --force
```

Se creará:

```text
Prueba Gratuita — $0.00
```

Después de probar el registro público, eliminarlo desde `/planes` o dejarlo deshabilitado en el seeder.

## 9. Permisos MySQL para tenants

El usuario `DB_USERNAME` debe poder crear bases y acceder a las bases de tenants.

Como administrador MySQL:

```sql
GRANT CREATE ON *.* TO 'adlatere_user'@'localhost';
GRANT ALL PRIVILEGES ON `tenant%`.* TO 'adlatere_user'@'localhost';
FLUSH PRIVILEGES;
```

Verificar:

```sql
SHOW GRANTS FOR 'adlatere_user'@'localhost';
```

Sin estos permisos aparece:

```text
1044 Access denied ... to database 'tenant...'
```

## 10. Nginx y PHP-FPM en CloudPanel

Archivo:

```bash
sudo nano /etc/nginx/sites-enabled/adlatere.facta.ec.conf
```

Debe incluir:

```nginx
server_name adlatere.facta.ec *.adlatere.facta.ec;

root /home/adminfacta/htdocs/adlatere/public;
index index.php index.html;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/clp-php-fpm.sock;
}
```

En este servidor el socket correcto es:

```text
/run/clp-php-fpm.sock
```

No usar `127.0.0.1:16000` si no hay un PHP-FPM escuchando allí.

Verificar:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

## 11. Permisos de storage en CloudPanel

El PHP-FPM de CloudPanel usa el usuario `clp`.

```bash
cd /home/adminfacta/htdocs/adlatere

sudo chown -R clp:clp storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

Verificar:

```bash
ls -ld storage storage/framework/cache/data storage/tenantvaldivieso
sudo -u clp test -w storage/framework/cache/data && echo "ESCRITURA OK"
```

Si se ejecuta Artisan como `root`, puede volver a crear archivos con dueño `root`. Después de ejecutar:

```bash
sudo php artisan optimize:clear
sudo php artisan config:cache
sudo php artisan view:cache

sudo chown -R clp:clp storage bootstrap/cache
```

## 12. DNS

Registros requeridos:

```text
A   adlatere.facta.ec          IP_DEL_SERVIDOR
A   *.adlatere.facta.ec        IP_DEL_SERVIDOR
```

En DigitalOcean, si se usa la zona `facta.ec`, el hostname del wildcard debe ser:

```text
*.adlatere
```

Verificar:

```bash
dig +short adlatere.facta.ec
dig +short valdivieso.adlatere.facta.ec
```

Si devuelve `NXDOMAIN`, falta el wildcard o la propagación.

## 13. Certificado SSL

El certificado debe incluir:

```text
adlatere.facta.ec
*.adlatere.facta.ec
```

Verificar:

```bash
sudo openssl x509 \
  -in /etc/nginx/ssl-certificates/facta.ec.crt \
  -noout -text | grep -A2 "Subject Alternative Name"
```

Si solo incluye el dominio central, los tenants tendrán error de certificado.

## 14. Crear tenant desde administración

1. Entrar a:

```text
https://adlatere.facta.ec/login
```

2. Usar el super admin central.

3. Ir a:

```text
https://adlatere.facta.ec/dash
```

4. Pulsar **Crear Tenant Manualmente**.

5. Completar:

```text
Nombre Empresa
Subdominio
Email Admin
Contraseña Admin
Plan
Precio
```

6. Guardar.

El flujo correcto:

```text
Tenant::create
→ TenantProvisioner::ensure
→ CREATE DATABASE tenant<subdominio>
→ tenants:migrate
→ tenants:seed
→ CreateTenantStorage
→ crear dominio
→ crear/actualizar admin
```

El admin usa el email y contraseña ingresados en el formulario.

## 15. Crear tenant desde registro público

1. Entrar a:

```text
https://adlatere.facta.ec
```

2. Elegir un plan.
3. Completar los datos del despacho y del administrador.
4. Si el plan es de pago, completar la pasarela.
5. Si es el plan de prueba de $0, se activa inmediatamente.
6. Entrar a:

```text
https://<subdominio>.adlatere.facta.ec/login
```

El admin usa el email y contraseña del registro.

## 16. Recuperar un tenant incompleto

Si el tenant existe en central pero no tiene base o dominio:

```bash
php artisan tinker --execute='$t = App\Models\Tenant::find("valdivieso"); if ($t) { app(App\Services\TenantProvisioner::class)->ensure($t); echo "Provisionado"; }'
```

Crear dominio si falta:

```bash
php artisan tinker --execute='$t = App\Models\Tenant::find("valdivieso"); if ($t) { $t->domains()->firstOrCreate(["domain" => "valdivieso.adlatere.facta.ec"]); echo "Dominio listo"; }'
```

Verificar base:

```bash
mysql -u adlatere_user -p -e "SHOW DATABASES LIKE 'tenantvaldivieso';"
php artisan tenants:run migrate:status --option="path=database/tenant"
```

## 17. Verificaciones posteriores

Central:

```bash
curl -k -I --resolve adlatere.facta.ec:443:127.0.0.1 \
  https://adlatere.facta.ec/login
```

Tenant:

```bash
curl -k -I --resolve valdivieso.adlatere.facta.ec:443:127.0.0.1 \
  https://valdivieso.adlatere.facta.ec/login
```

Debe responder `200` o `302` y las cabeceras de Adlatere.

Logs:

```bash
tail -n 100 storage/logs/laravel.log
tail -n 100 storage/tenantvaldivieso/logs/laravel.log
sudo tail -n 100 /var/log/nginx/error.log
sudo journalctl -u clp-php-fpm -n 100 --no-pager
```

## 18. Errores corregidos y solución

### `Invalid route action: [App\http\Livewire\TiposActividades]`

Causa: mayúsculas incorrectas en namespace.

Solución:

```php
use App\Http\Livewire\TiposActividades;
```

Luego:

```bash
composer dump-autoload -o
php artisan optimize:clear
```

### `Failed to open the referenced table 'tareas'`

Causa: migración de tenant ejecutada en la base central.

Solución:

- `tarea_historiales` debe estar solo en `database/tenant`.
- Eliminar de `database/migrations`.
- Ejecutar central y después tenants.

### `Can't DROP 'invoice_limit'`

Causa: dos migraciones agregando la misma columna.

Solución: `down()` idempotente con `Schema::hasColumn`.

### `1044 Access denied ... CREATE DATABASE 'tenant...'`

Causa: usuario MySQL sin permisos.

Solución:

```sql
GRANT CREATE ON *.* TO 'adlatere_user'@'localhost';
GRANT ALL PRIVILEGES ON `tenant%`.* TO 'adlatere_user'@'localhost';
FLUSH PRIVILEGES;
```

### `DNS_PROBE_FINISHED_NXDOMAIN`

Causa: no existe DNS wildcard.

Solución: crear `*.adlatere.facta.ec`.

### `connect() to unix:/...php8.2-fpm.sock failed`

Causa: Nginx apunta a un socket que no existe.

Solución: usar el socket real:

```nginx
fastcgi_pass unix:/run/clp-php-fpm.sock;
```

### `tlsv1 unrecognized name` con `curl`

Causa: se está usando la IP como SNI.

Solución: usar `--resolve`:

```bash
curl -k -I --resolve valdivieso.adlatere.facta.ec:443:127.0.0.1 \
  https://valdivieso.adlatere.facta.ec/login
```

### `file_put_contents(...storage/framework/cache...): Permission denied`

Causa: storage pertenece a `root`.

Solución:

```bash
sudo chown -R clp:clp storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

### 500 en tenant pero 200 en central

Causa: Nginx no tiene wildcard en `server_name` o el tenant no tiene storage.

Solución:

- Agregar `*.adlatere.facta.ec`.
- Recargar Nginx.
- Verificar storage del tenant.

## 19. Seguridad y operación

- Cambiar contraseñas por defecto del super admin y del admin de tenants.
- Mantener `APP_DEBUG=false`.
- No exponer MySQL a Internet.
- No usar `migrate:refresh` en producción.
- Configurar respaldo diario de base central, bases de tenants y `storage`.
- Usar HTTPS wildcard.
- Revisar logs después de cada despliegue.
- Ejecutar Artisan como root solo cuando sea necesario y corregir permisos después.

## 20. Checklist de despliegue

```text
[ ] Código actualizado
[ ] Composer y autoload actualizados
[ ] .env de producción configurado
[ ] config/tenancy.php incluye dominio central
[ ] Migraciones centrales ejecutadas
[ ] Super admin y planes creados
[ ] Permisos MySQL para tenant%
[ ] DNS wildcard
[ ] Certificado wildcard
[ ] Nginx con server_name wildcard
[ ] PHP-FPM correcto
[ ] Permisos storage clp:clp
[ ] Caché de configuración/vistas regenerada
[ ] Prueba central 200/302
[ ] Prueba tenant 200/302
[ ] Login de tenant correcto
[ ] Respaldos configurados
```
