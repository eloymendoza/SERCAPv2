# SERCAP V2 (backend)

## Configuración inicial del proyecto

1. Se decidió utilizar la versión 8.5.3 NTS x64 de PHP (no la TS), para que funcione de manera óptima con nginx, por lo que, puedes descargarla desde [este enlace](https://downloads.php.net/~windows/releases/archives/).
2. Una vez descargado, descomprimes el zip y lo colocas junto tus otras versiones de php y puedes elegir cómo gestionarlas. Una opción puede ser crear un script que te ayude a cambiar rápidamente entre versiones, como por ejemplo:

    **En un archivo llamado use-php82.bat (o algo por el estilo):**

    ``` powershell
    @echo off
    rmdir C:\php\active
    mklink /D C:\php\active C:\php\php-8.2.27-Win32-vs16-x64
    php -v
    ```

    Y hacer esto para cada versión que utilices en tu equipo.

    > [!NOTE]
    > *Si quieres saber más, puedes preguntar a Eloy o a Alda*

3. Descarga los dll 5.13.0 de SQL Server en [este link](https://learn.microsoft.com/en-us/sql/connect/php/release-notes-php-sql-driver?view=sql-server-ver17#previous-releases).
4. Descomprime el zip y busca:
    - php_sqlsrv_85_nts_x64.dll
    - php_pdo_sqlsrv_85_nts_x64.dll
5. Cópialos y pégalos en la carpeta `\ext` de tu versión de php.
6. En caso de no contar con el archivo `cacert-2025-12-02.pem`, solicítalo a Eloy o Alda.
7. Una vez que lo tengas, dirígete a `\extras\ssl` en tu versión de php y pégalo ahí.
8. Haz una copia del archivo `php.ini-dev`, cambia el nombre a `php.ini` y asegúrate de lo siguiente

    ``` ini
    # Que esto esté descomentado
    extension_dir = "./ext"

    extension=curl
    extension=fileinfo
    extension=mbstring
    extension=openssl
    extension=php_sqlsrv_85_nts_x64
    extension=php_pdo_sqlsrv_85_nts_x64
    extension=zip
    ```

    ``` ini
    # En estas variables, agregar las rutas al cacert.pem
    curl.cainfo = "C:\php\php-8.5.3-nts-Win32-vs17-x64\extras\ssl\cacert-2025-12-02.pem" (la ruta exacta donde tienes el archivo)
    openssl.cafile = "C:\php\php-8.5.3-nts-Win32-vs17-x64\extras\ssl\cacert-2025-12-02.pem" (lo mismo aquí)
    ```
9. Clona el proyecto de github en tu equipo
10. Ya que tengas tu repositorio, ejecuta el siguiente comando:

    ``` bash
    # Crea una copia del .env de ejemplo para que el proyecto pueda utilizar este archivo.
    cp .env.example .env
    ```
11. Pide a Eloy o Aldair los valores reales de este archivo
12. Ejecuta:

    ``` bash
    # Instala dependencias php del proyecto.
    composer install

    # Para verificar que todo esté correcto, ejecuta el proyecto en el puerto que le indiques (ej.):
    php artisan serve --port 85
    ```

13. En tu navegador, dirígete a la ruta que te indique la terminal. Si aparece la información de Laravel, el proyecto fue configurado correctamente.

## Despliegue con nginx y NSSM
1. Descarga [nginx](https://nginx.org/en/download.html) (v1.26) y [NSSM](https://nssm.cc/download) (v2.24)
2. Crea un directorio propio para cada uno (ej. `C:\nginx` y `C:\nssm`) y descomprime los zip en su directorio correspondiente

### Configuración de nginx
3. Para verificar que nginx funciona correctamente:

    ``` bash
    # Cambia al directorio de nginx
    cd C:\nginx

    # Ejecuta el servidor, pero antes asegúrate que IIS esté detenido en tu equipo
    start nginx

    # Para validar si está corriendo nginx. Si aparecen elementos, quiere decir que todo correcto
    tasklist /fi "imagename eq nginx.exe"

    # Otra manera, es en tu navegador, poner localhost sin ningún puerto, y deberías ver el mensaje: "Welcome to nginx!" 
    ```

4. Genera los certificados para el dominio que utilizarás localmente (ej. sercapv2-<tunombre>pc.grupo-iai.com.mx):

    4.1 Abre una terminal como administrador e instala mkcert
    ``` powershell
    choco install mkcert
    ```
    4.2 Ejecuta lo siguiente para crear un CA local en el sistema
    ``` powershell
    mkcert -install
    ```
    4.3 Crea un nuevo directorio para alojar tus certificados (ej. `C:\nginx\certificados`)

    4.4 Muévete a ese nuevo directorio
    ``` powershell
    cd C:\nginx\certificados
    ```
    4.5 Genera los certificados
    ``` powershell
    mkcert sercapv2-<tunombre>pc.grupo-iai.com.mx
    ```
    4.6 Esto te va a generar los siguientes archivos en tu directorio
    ``` powershell
    sercapv2-<tunombre>pc.grupo-iai.com.mx.pem
    sercapv2-<tunombre>pc.grupo-iai.com.mx-key.pem
    ```

5. Dirígete a `C:\nginx\conf\nginx.conf`
6. Ingresa lo siguiente:

    ```
    # Optimización de procesos según el hardware
    worker_processes auto;
    
    events {
        worker_connections 1024;
    }
    
    http {
        include mime.types;
        default_type application/octet-stream;
    
        # FORMATO DE LOG PERSONALIZADO para distinguir puertos en archivos unificados
        log_format vhost_combined '$remote_addr - $remote_user [$time_local] '
                                '"$request" $status $body_bytes_sent '
                                '"$http_referer" "$http_user_agent" '
                                'Port:$server_port';
    
        # LOG GLOBAL
        error_log logs/nginx.error.log warn;
    
        # --- OPTIMIZACIONES ---
        sendfile on;
        keepalive_timeout 65;
        server_names_hash_bucket_size 64;
        server_tokens off; # Seguridad: oculta la versión de Nginx
        # --- COMPRESIÓN PARA AGILIZAR CARGA INICIAL ---
        gzip on;
        gzip_proxied any;
        gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
        gzip_vary on;
        gzip_comp_level 5;
    
        # --- AJUSTES DE BUFFER PARA SISTEMAS CARGADOS ---
        proxy_buffers 8 16k;
        proxy_buffer_size 32k;
    
        # --- DEFINICIÓN DE BACKENDS (UPSTREAMS) ---
        # Centralizar aquí permite cambiar puertos sin buscar en todo el archivo
        upstream sercapv2_backend { server 127.0.0.1:9002; }
        upstream sercapv2_frontend { server 127.0.0.1:3000; }
    
    
        # --- CONFIGURACIÓN SSL GLOBAL ---
        # Se define una vez para todos los servidores
        ssl_certificate C:/nginx/certificados/sercapv2-<tunombre>pc.grupo-iai.com.mx.pem;
        ssl_certificate_key C:/nginx/certificados/sercapv2-<tunombre>pc.grupo-iai.com.mx-key.pem;
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers HIGH:!aNULL:!MD5;
        ssl_session_cache shared:SSL:10m;
        ssl_session_timeout 10m;
    
        # =============================================================
        # 0. BLOQUE DE SEGURIDAD (DEFAULT)
        # Bloquea accesos por IP o nombres de red internos (ej. iaipc130-pc)
        # =============================================================
        server {
            listen 80 default_server;
            listen 443 ssl default_server;
            server_name _;
    
            access_log logs/default.access.log;
            error_log logs/default.error.log warn;
    
            return 444;
        }
    

    # =============================================================
        # SERVIDOR 5: SERCAPv2 (Laravel)
    # =============================================================
    # -----------------------------
    # REDIRECCIÓN HTTP → HTTPS
    # -----------------------------
    server {
    listen 80;
    server_name sercapv2-<tunombre>pc.grupo-iai.com.mx;
    return 301 https://$host$request_uri;
    }
    
    # -----------------------------
    # SERVIDOR PRINCIPAL HTTPS
    # -----------------------------
    server {
    listen 443 ssl;
    server_name sercapv2-<tunombre>pc.grupo-iai.com.mx;
    root C:/Proyectos/SERCAPv2/public;
    index index.php index.html;
    
    # -----------------------------
    # API (Laravel)
    # -----------------------------
    location ^~ /api {
    try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ^~ /sanctum {
    try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ^~ /up {
    try_files $uri $uri/ /index.php?$query_string;
    }
    
    # -----------------------------
    # PHP (Laravel)
    # -----------------------------
    location ~ \.php$ {
    fastcgi_pass sercapv2_backend;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    
    # Estos tres son críticos para cookies y sesiones
    fastcgi_param HTTPS on;
    fastcgi_param SERVER_PORT 443;
    fastcgi_param HTTP_X_FORWARDED_PROTO https;  # Laravel detecta HTTPS real
    
    fastcgi_read_timeout 300;
    }
    
    # -----------------------------
    # FRONTEND (React Dev Server)
    # -----------------------------
    location / {
    proxy_pass http://sercapv2_frontend;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto https;
    }
    }

    }
    ```
7. En los .env, debes tener de esta manera en estas variables:
    ``` bash  
    # Backend
    APP_URL=https://sercapv2-<tunombre>pc.grupo-iai.com.mx
    SANCTUM_STATEFUL_DOMAINS=sercapv2-<tunombre>pc.grupo-iai.com.mx

    # Frontend
    VITE_APP_API_URL=https://sercapv2-<tunombre>pc.grupo-iai.com.mx/api
    VITE_APP_HOST=sercapv2-<tunombre>pc.grupo-iai.com.mx
    ```

8. En el archivo `cors.php`, en `allowed_origins`, se tiene que definir tu dominio, o buscar que esto sea dinámico

### Configuración de NSSM

9. Ejecuta una terminal como administrador y dirígete a donde tienes el .exe de nssm
    ``` bash  
    # Ejemplo
    cd C:\nssm\win64
    ```
10. Instala tus servicios:

    **nginx**

    10.1 Ejecuta:
    ``` bash  
    .\nssm install nginx
    ```
    10.2 Se abrirá una ventana de nssm donde configurarás lo siguiente:
    ``` bash  
    # Pestaña Aplicación
    Path: C:\nginx\nginx.exe
    Startup directory: C:\nginx

    # Pestaña I/O
    C:\nssm\win64\logs\nginx\nginx_out_log # Ruta a un directorio para guardar logs por servicio
    C:\nssm\win64\logs\nginx\nginx_error_log # Ruta a un directorio para guardar logs por servicio
    ```

    **PHP 8.5.3 NTS**

    10.3 Ejecuta:
    ``` bash  
    .\nssm install PHP8.5.3-NTS
    ```
    10.4 Se abrirá una ventana de nssm donde configurarás lo siguiente:
    ``` bash  
    # Pestaña Aplicación
    Path: C:\php\php-8.5.3-nts\php-cgi.exe
    Startup directory: C:\php\php-8.5.3-nts
    Arguments: -b 127.0.0.1:9002 # o el puerto de tu elección

    # Pestaña I/O
    C:\nssm\win64\logs\PHP8.5.3-NTS\php_cgi_out.log # Ruta a un directorio para guardar logs por servicio
    C:\nssm\win64\logs\PHP8.5.3-NTS\php_cgi_error.log # Ruta a un directorio para guardar logs por servicio
    ```

11. Si todo se configuró correctamente, ejecuta:
    ``` bash  
    .\nssm start nginx
    .\nssm start PHP8.5.3-NTS
    ```
12. Corre el proyecto del frontend con `npm run dev`
13. Dirígete a tu dominio y verifica que se ejecute todo correctamente
