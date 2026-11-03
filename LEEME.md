# AERIA Finance — Cheques · Primera etapa (código real)

## Alcance de esta entrega

- Nuevo menú **Cheques** (móvil y escritorio), fuera del middleware `financial.day`.
- Liquidaciones de **un cheque de tercero por operación**: papel o Echeq.
- Simular en pantalla sin guardar; guardar borradores, ver histórico y **retener/liberar** una liquidación.
- Multiempresa con `BelongsToCompany` y alcance global `CompanyScope` ya existentes en AERIA.
- **Sin** egresos, ingresos, contabilización, desembolsos, recepción de cheques ni modificaciones de saldos.
- Impuestos desglosados como **impuesto al cheque e Ingresos Brutos**, nunca como comisión.
- Fórmula inicial igual al ejemplo Prestar: interés simple ACT/365 y 2 días de clearing configurable en número de días, sin calendario bancario automático.
- El IVA del ejemplo usa como base intereses + ambos impuestos, pero **no presume** que ese tratamiento sea fiscalmente válido en todos los casos.

## Instalación segura en Laragon (antes de producción)

1. Hacé un commit o respaldo de tu código local actual. **No reemplaces `routes/web.php`, `sidebar.blade.php` ni `resources/js/app.js` con archivos viejos del ZIP original**.
2. Extraé las **carpetas `app/`, `database/`, `resources/` y `tests/`** de este paquete en la raíz del proyecto. Son archivos nuevos. Comprobá que no haya colisiones.
3. Desde Git Bash en la raíz del proyecto, aplicá el parche mínimo para los tres archivos existentes:
   ```bash
   git apply --check CAMBIOS_EXISTENTES.patch
   git apply CAMBIOS_EXISTENTES.patch
   ```
   Si `--check` falla porque tus archivos fueron modificados desde el ZIP de referencia, **no fuerces el parche**. Leé el mismo archivo `.patch` e insertá sus tres cambios manualmente.
4. Comprobá migraciones antes de ejecutar:
   ```bash
   php artisan migrate:status
   php artisan migrate --pretend
   ```
   Luego **solo en desarrollo**:
   ```bash
   php artisan migrate
   php artisan route:list --name=cheques
   php artisan test --filter=ChequeLiquidationCalculatorTest
   npm run build
   ```
5. Ingresá a `/cheques/nueva` y probá:
   - Cliente de prueba, fecha 2026-11-02, vencimiento 2026-12-02, nominal 100000,
     tasa 62, clearing 2, impuesto al cheque 1.2, IIBB 0.6, IVA 21.
   - Resultado: interés 5.435,62; impuesto al cheque 1.200,00; IIBB 600,00; IVA 1.519,48; neto 91.244,90.
   - Guardar borrador: el listado debe mostrarlo; retener/liberar cambia estado e historial.
   - Cambiar de empresa: no debe poder verse una liquidación de la empresa anterior (incluida URL directa).

## Archivos nuevos

- `app/Services/Cheques/ChequeLiquidationCalculator.php`
- `app/Http/Controllers/ChequeLiquidationController.php`
- `app/Models/Cheque.php`, `ChequeLiquidation.php`, `ChequeLiquidationEvent.php`
- `database/migrations/2026_11_02_235500_create_cheque_module_tables.php`
- `resources/views/cheques/{index,create,show}.blade.php`
- `resources/css/cheques.css`
- `tests/Unit/ChequeLiquidationCalculatorTest.php`

## Cambios mínimos en archivos existentes

- `routes/web.php`: importar el controlador y añadir 7 rutas bajo `auth`, `company` y `company.access`, *fuera de `financial.day`*.
- `resources/views/layouts/sidebar.blade.php`: enlace Cheques en ambos menús.
- `resources/js/app.js`: importar `resources/css/cheques.css`. No hace falta entrada adicional en `vite.config.js`.

## Pendiente para etapa 2

- Agregar múltiples cheques en una misma liquidación, cliente formal, tasas/impuestos configurados por empresa.
- Validación operativa y movimiento real con controles transaccionales, pago idempotente, conciliación bancaria.
- Estados de cartera, vencimiento/acreditación/rechazo, días hábiles/feriados y clasificación contable/impositiva legal.

**No publicarlo aún en producción sin pasar las pruebas de integración en tu proyecto actualizado.**
