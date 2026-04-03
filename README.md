# Registro de salidas de productos

Aplicación web para **registrar salidas de mercadería por producto** y consultar un **historial administrable**.

El proyecto tiene 2 experiencias principales:

- **Pantalla de empleado (pública)**: registrar una salida (producto, cantidad, empleado opcional, observación opcional) con previsualización antes de guardar.
- **Panel administrador (con login)**: ver métricas, filtrar por producto y fechas, revisar historial paginado y eliminar registros.

---

## Stack y herramientas

- **Backend**: PHP 8.2+ · Laravel 11
- **Templates/UI**: Blade · Tailwind CSS (con `@tailwindcss/forms`)
- **Auth scaffolding**: Laravel Breeze (Blade + Tailwind + Alpine)
- **Assets**: Vite 6 + `laravel-vite-plugin`
- **JS**: Alpine.js (cargado globalmente) + scripts vanilla en vistas
- **DB**:
	- Por defecto (según `.env.example`): **SQLite**
	- Con Docker (Sail): **MySQL 8** (ver `compose.yaml`)
- **Infra local**: Laravel Sail (Docker)
- **Calidad**: PHPUnit · Laravel Pint

---

## UI: paleta y tipografía (design tokens)

La UI usa **tokens semánticos** basados en **variables CSS** para mantener consistencia visual y soportar **modo claro/oscuro automáticamente** (según `prefers-color-scheme`).

- **Tipografía**: `Figtree` (importada en `resources/css/app.css` y configurada como `fontFamily.sans` en `tailwind.config.js`).
- **Colores**: definidos como variables `--impobam-*` en `resources/css/app.css` y expuestos como colores Tailwind en `tailwind.config.js`.

Clases principales (usar estas en vez de `gray-*`, `slate-*`, `indigo-*`, etc.):

- **Superficies y texto**: `bg-canvas`, `bg-surface`, `bg-muted`, `border-border`, `border-border-strong`, `text-foreground`, `text-foreground-muted`.
- **Acciones/estados**:
	- `primary`: `bg-primary` / `hover:bg-primary-hover` + `text-primary-foreground`
	- `success`: `bg-success` / `hover:bg-success-hover` + `text-success-foreground`
	- `danger`: `bg-danger` / `hover:bg-danger-hover` + `text-danger-foreground`
	- `accent`: `bg-accent` / `hover:bg-accent-hover` + `text-accent-foreground`
	- `warning`: `bg-warning` / `hover:bg-warning-hover` + `text-warning-foreground`
- **Variantes suaves y bordes**: `bg-*-soft` y `border-*-border` para cajas informativas, estados y resaltados.

---

## Funcionalidades (lo que hace hoy)

### 1) Registro de salida (empleado)

- Entrada del sistema: `GET /` (pantalla con 2 botones)
- Registro de salidas: `GET /salidas`
- Muestra un formulario para registrar una salida:
	- `product_id` (requerido)
	- `quantity` (requerido, mínimo 1)
	- `employee_name` (opcional)
	- `notes` (opcional)
- Antes de guardar, el usuario puede abrir un **modal de confirmación** (“Revisar registro”).
- Se muestran los **últimos 5 registros** (colapsable/expandible).

Detalles:

- El selector de productos lista **solo** productos con `is_active = true`.
- Validaciones (backend): `employee_name` máx. 120 caracteres, `notes` máx. 500.

**Eliminar el último registro (solo el propio)**

- Cuando un empleado guarda una salida, la app guarda en sesión `employee_last_output_id`.
- En la lista de “últimos 5”, solo aparece el botón de eliminar para ese último ID.
- La eliminación usa una **URL firmada temporal** (firma válida por ~30 min) y además se valida que el ID coincida con la sesión.

### 2) Panel administrador

- Requiere autenticación.
- Ruta: `GET /dashboard`
- Incluye:
	- Métricas: total de registros, total de unidades, registros de hoy, unidades de hoy.
	- Filtros: producto, desde/hasta (por fecha de `moved_at`).
	- Historial paginado (15 por página) con opción de **eliminar** registros.

Nota: actualmente **cualquier usuario autenticado** puede acceder al panel (no hay roles/permisos adicionales implementados).

### 3) Ventas (pública)

- Entrada: `GET /ventas`
- Permite registrar una venta con:
	- `customer_name` (**obligatorio**)
	- `delivery_location` (opcional)
	- múltiples productos en una sola venta (`items[]`)
	- `unit_price` por ítem (variable por venta)
	- cálculo automático de subtotales y total
	- pago inicial opcional
	- pagos parciales posteriores
	- **fecha prometida** cuando queda saldo pendiente

Rutas:

- `GET /ventas` → formulario
- `POST /ventas` → guardar venta
- `GET /ventas/{sale}` → detalle + historial de pagos
- `POST /ventas/{sale}/pagos` → registrar pago parcial

---

## Modelo de datos

### `products`

- `name` (string) **único**
- `is_active` (boolean) default `true`

### `stock_outputs`

- `product_id` (FK a `products`, con `cascadeOnDelete`)
- `quantity` (unsigned int)
- `employee_name` (string, nullable)
- `notes` (text, nullable)
- `moved_at` (timestamp)

Relaciones:

- `Product` tiene muchas `StockOutput`
- `StockOutput` pertenece a `Product`

### `sales`

- `customer_name` (string)
- `delivery_location` (string, nullable)
- `due_date` (date, nullable) — fecha prometida cuando queda saldo
- `total_amount` (decimal 12,2)

### `sale_items`

- `sale_id` (FK a `sales`, cascadeOnDelete)
- `product_id` (FK a `products`, restrictOnDelete)
- `quantity` (unsigned int)
- `unit_price` (decimal 12,2)
- `line_total` (decimal 12,2)

### `sale_payments`

- `sale_id` (FK a `sales`, cascadeOnDelete)
- `amount` (decimal 12,2)
- `paid_at` (timestamp)

---

## Rutas principales

Definidas en `routes/web.php`:

- `GET /` → pantalla inicial (botones: salidas / ventas)
- `GET /salidas` → formulario de salida (empleado)
- `POST /salidas` → guardar salida
- `DELETE /salidas/{stockOutput}` → eliminar **solo** el último registro del empleado (requiere URL firmada)
- `GET /ventas` → formulario de ventas
- `POST /ventas` → guardar venta
- `GET /ventas/{sale}` → detalle + pagos
- `POST /ventas/{sale}/pagos` → guardar pago
- `GET /dashboard` → panel admin (auth)
- `DELETE /dashboard/salidas/{stockOutput}` → eliminar registro desde admin (auth)

Auth (Breeze) en `routes/auth.php`: `/login`, `/register`, `/logout`, etc.

---

## Instalación y ejecución (local, recomendado para empezar)

### Requisitos

- PHP 8.2+
- Composer
- Node.js (recomendado 18+)

### Pasos

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# SQLite (por defecto)
touch database/database.sqlite

php artisan migrate --seed
```

### Desarrollo

Este repo incluye un script cómodo que levanta todo en paralelo:

```bash
composer dev
```

Eso ejecuta:

- `php artisan serve`
- `php artisan queue:listen --tries=1`
- `php artisan pail --timeout=0`
- `npm run dev` (Vite)

Luego abre `http://localhost:8000` (o el puerto que indique `artisan serve`).

---

## Instalación con Docker (Laravel Sail)

Sail está configurado en `compose.yaml` con un servicio MySQL 8.

1) Instalar dependencias (si aún no existen):

```bash
composer install
```

2) Copiar variables y ajustar DB a MySQL (en `.env`):

- `DB_CONNECTION=mysql`
- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_DATABASE=laravel`
- `DB_USERNAME=sail`
- `DB_PASSWORD=password`

3) Levantar contenedores:

```bash
./vendor/bin/sail up -d
```

4) Migrar y sembrar:

```bash
./vendor/bin/sail artisan migrate --seed
```

5) Para assets con HMR:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Abrir `http://localhost`.

---

## Seeder de productos

El seeder `ProductSeeder` crea (si no existen) varios productos de ejemplo.

```bash
php artisan db:seed
# o específicamente
php artisan db:seed --class=ProductSeeder
```

---

## Crear un usuario para el panel admin

Puedes registrarte en `/register` o crear un usuario rápido con Tinker:

```bash
php artisan tinker
```

```php
use App\Models\User;

User::create([
	'name' => 'Admin',
	'email' => 'admin@example.com',
	'password' => 'secret',
]);
```

Luego ingresa en `/login` y abre `/dashboard`.

---

## Tests y calidad

```bash
php artisan test
```

Formateo de código (Pint):

```bash
./vendor/bin/pint
```

---

## Notas / troubleshooting

- Si al eliminar un registro aparece **“Invalid signature”**, revisa `APP_URL` en tu `.env` y que estés usando el mismo host/puerto con el que se generó la URL firmada.
- La eliminación “de empleado” depende de la **sesión del navegador**: si cambias de navegador/dispositivo o limpias cookies, no podrás borrar ese último registro.

