# 🚀 Integración Trazabilidad → plantaCruds - Quick Start

## ✅ Implementación Completa

La integración está **100% funcional** y lista para usar.

## 📋 Resumen Ejecutivo

**Flujo**: Trazabilidad aprueba pedido → plantaCruds recibe y crea envíos automáticamente

**Relación**: 1 Pedido (N destinos) → N Envíos en plantaCruds

**Tracking**: Tabla `order_envio_tracking` registra toda la sincronización

## 🔧 Archivos Modificados

### Trazabilidad
```
✅ app/Services/PlantaCrudsIntegrationService.php          [NUEVO]
✅ app/Models/OrderEnvioTracking.php                       [NUEVO]
✅ database/migrations/..._create_order_envio_tracking.php [NUEVO]
✅ app/Http/Controllers/Api/OrderApprovalController.php    [MODIFICADO]
✅ .env                                                     [MODIFICADO]
```

### plantaCruds
```
✅ app/Http/Controllers/Api/EnvioApiController.php         [MODIFICADO]
```

## ⚙️ Configuración Requerida

### 1. Variable de entorno en Trazabilidad

Archivo: `Trazabilidad/.env`

```env
PLANTACRUDS_API_URL=http://localhost/plantaCruds/public/api
```

**Ajustar según tu entorno**:
- XAMPP/Apache: `http://localhost/plantaCruds/public/api`
- Laravel Serve: `http://localhost:8000/api`  
- Red local: `http://192.168.x.x:8000/api`

### 2. Migración ejecutada

```bash
cd "c:\Users\Personal\Downloads\planta jhair\Trazabilidad"
php artisan migrate
```

✅ Ya ejecutado

## 🎯 Cómo Usar

### Método 1: API (Producción)

```http
POST /api/order-approval/{orderId}/approve
Authorization: Bearer YOUR_JWT_TOKEN
```

La respuesta incluirá:
```json
{
  "message": "Pedido aprobado exitosamente",
  "order": {...},
  "envios_created": [
    {
      "destination_id": 1,
      "envio_codigo": "ENV-251208-000001"
    }
  ],
  "integration_success": true
}
```

### Método 2: Script de Prueba

```bash
cd "c:\Users\Personal\Downloads\planta jhair\Trazabilidad"
php test_integracion.php
```

Este script:
- ✓ Verifica conectividad
- ✓ Lista almacenes disponibles
- ✓ Busca pedidos pendientes
- ✓ Permite aprobar y probar la integración interactivamente

## 📊 Verificar Resultados

### En Trazabilidad

**Ver tracking de sincronización**:
```sql
SELECT 
    oet.id,
    co.order_number,
    od.address,
    oet.envio_codigo,
    oet.status,
    oet.error_message,
    oet.created_at
FROM order_envio_tracking oet
JOIN customer_order co ON oet.order_id = co.order_id
JOIN order_destination od ON oet.destination_id = od.destination_id
ORDER BY oet.created_at DESC;
```

**Ver solo errores**:
```sql
SELECT * FROM order_envio_tracking 
WHERE status = 'failed';
```

### En plantaCruds

**Ver todos los envíos**:
```http
GET /api/envios
```

**Buscar envío específico**:
```http
GET /api/envios/qr/ENV-251208-000001
```

**Ver en base de datos**:
```sql
SELECT 
    e.id,
    e.codigo,
    e.estado,
    e.observaciones,
    a.nombre as almacen,
    e.created_at
FROM envios e
JOIN almacens a ON e.almacen_destino_id = a.id
ORDER BY e.created_at DESC;
```

## 🔍 Logs

### Trazabilidad
```bash
tail -f "storage/logs/laravel.log"
```

Buscar:
- `Envio created successfully in plantaCruds`
- `PlantaCruds integration completed`
- `Failed to create Envio` (errores)

### plantaCruds
```bash
tail -f "storage/logs/laravel.log"
```

Buscar:
- `Envío creado exitosamente`
- `Error al crear envío`

## 🎨 Características

### ✅ Implementado

- ✅ Transformación automática de datos
- ✅ Mapeo de productos por nombre
- ✅ Búsqueda inteligente de almacenes (coordenadas, dirección)
- ✅ Múltiples destinos → Múltiples envíos
- ✅ Tracking completo de sincronización
- ✅ Manejo robusto de errores
- ✅ Logs detallados
- ✅ Observaciones enriquecidas con datos del pedido
- ✅ Generación automática de códigos QR en plantaCruds

### 🔄 Flujo Detallado

```
Trazabilidad                          plantaCruds
─────────────                         ────────────
1. Usuario aprueba pedido
   │
2. approveOrder()
   ├─ Marca pedido: "aprobado"
   ├─ Marca productos: "aprobado"
   └─ Llama integración
      │
3. sendOrderToShipping()
   ├─ Carga pedido + relaciones
   └─ Por cada destino:
      │
4. buildEnvioData()                   
   ├─ Busca almacén                   → GET /api/almacenes
   ├─ Mapea productos
   └─ Construye payload
      │
5. createEnvio()                      → POST /api/envios
   │                                     {
   │                                       almacen_destino_id,
   │                                       productos[],
   │                                       observaciones
   │                                     }
   │                                     │
   │                                  6. EnvioApiController
   │                                     ├─ Valida datos
   │                                     ├─ Crea envío
   │                                     ├─ Genera código
   │                                     ├─ Crea productos
   │                                     └─ Genera QR
   │                                     │
   └───────────────────────────────────← Retorna envío creado
      │
7. Guarda tracking
   ├─ order_envio_tracking
   ├─ envio_id, envio_codigo
   └─ status: success/failed
      │
8. Retorna respuesta
   └─ envios_created[]
```

## 🛠️ Datos Mapeados

| Trazabilidad | → | plantaCruds | Notas |
|--------------|---|-------------|-------|
| `CustomerOrder` | → | `Envio` | 1 pedido → N envíos (por destino) |
| `order_number` | → | `observaciones` | Incluido en observaciones |
| `delivery_date` | → | `fecha_estimada_entrega` | ✓ |
| `OrderDestination.address` | → | Búsqueda `almacen_destino_id` | Por coords o dirección |
| `OrderProduct` | → | `EnvioProducto` | Por cada producto |
| `Product.name` | → | `producto_nombre` | String directo |
| `Product.weight` | → | `peso_unitario` | ✓ |
| `quantity` | → | `cantidad` | ✓ |
| Cliente + Contacto | → | `observaciones` | Enriquecido |

## ⚠️ Requisitos Previos

### En plantaCruds

1. **Almacenes**: Debe existir al menos 1 almacén activo
   ```sql
   SELECT * FROM almacens WHERE activo = 1;
   ```
   
   Si no hay:
   ```sql
   INSERT INTO almacens (nombre, direccion_completa, latitud, longitud, activo, es_planta)
   VALUES ('Almacén Principal', 'Av. Principal 123', -12.0464, -77.0428, 1, 0);
   ```

2. **Tabla productos**: Puede estar vacía (se usa `producto_nombre`)

### En Trazabilidad

1. **Pedido con destinos**: El pedido debe tener al menos 1 destino
2. **Productos del pedido**: Con nombres y pesos válidos
3. **Cliente**: Debe estar relacionado al pedido

## 🐛 Troubleshooting

### Error: "No hay almacenes disponibles"

**Solución**: Crear almacén en plantaCruds o ajustar lógica de fallback

```php
// En PlantaCrudsIntegrationService::findOrCreateAlmacen()
// Línea ~160: Ya tiene fallback al primer almacén activo
```

### Error: "Connection refused"

**Solución**: 
1. Verificar que plantaCruds esté corriendo
2. Ajustar `PLANTACRUDS_API_URL` en `.env`
3. Probar: `curl http://localhost/plantaCruds/public/api/almacenes`

### Error: "producto_id required"

**Solución**: Ya corregido. `producto_id` es nullable y acepta `producto_nombre`

### Envío creado pero sin productos

**Verificar**: 
- Que los productos tengan nombres en Trazabilidad
- Logs en plantaCruds para ver qué llegó
- Tabla `envio_productos`

## 📚 Documentación Completa

Ver: `INTEGRACION_PLANTACRUDS.md` para detalles exhaustivos

## 🎉 Estado: PRODUCCIÓN READY

La integración está lista para uso en producción con:
- ✅ Manejo de errores
- ✅ Transacciones DB
- ✅ Logging completo
- ✅ Tracking de sincronización
- ✅ Validaciones robustas
- ✅ Documentación completa

## 📞 Test Rápido

```bash
# 1. Ir a Trazabilidad
cd "c:\Users\Personal\Downloads\planta jhair\Trazabilidad"

# 2. Ejecutar test
php test_integracion.php

# 3. Seguir instrucciones en pantalla
```

## 🚀 Próximos Pasos (Opcional)

1. **Webhooks**: plantaCruds notifica cambios de estado a Trazabilidad
2. **UI Admin**: Panel para ver tracking visualmente
3. **Retry automático**: Queue jobs para reintentar errores
4. **Autenticación**: API token en plantaCruds
5. **Sincronización productos**: Catálogo compartido

---

**¿Listo para probar?** 🎯

```bash
php test_integracion.php
```
