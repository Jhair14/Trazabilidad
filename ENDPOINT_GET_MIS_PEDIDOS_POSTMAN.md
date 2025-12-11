# 📋 Endpoint GET - Ver Mis Pedidos (Público)

## 🔗 Información del Endpoint

**URL:** `GET /api/customer-orders/by-user`

**Base URL:** `http://127.0.0.1:8001` (o tu URL del servidor)

**URL Completa:** `http://127.0.0.1:8001/api/customer-orders/by-user`

---

## 🔐 Autenticación

Este endpoint **NO requiere autenticación**. Es completamente público.

**Identificador:** Usa el `nombre_usuario` como parámetro de query para identificar los pedidos del cliente.

---

## 📋 Headers Requeridos

```
Accept: application/json
```

**Nota:** No se requiere el header `Authorization`.

---

## 🔍 Parámetros de Query (Requeridos)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
|-----------|------|-----------|-------------|---------|
| `nombre_usuario` | string | ✅ **Sí** | Nombre del usuario que creó los pedidos. Debe coincidir con el nombre usado al crear el pedido | `?nombre_usuario=Juan` |
| `per_page` | integer | ❌ No | Cantidad de resultados por página (paginación). Por defecto: 15 | `?nombre_usuario=Juan&per_page=50` |
| `page` | integer | ❌ No | Número de página (para paginación). Por defecto: 1 | `?nombre_usuario=Juan&page=2` |

### Ejemplos de URLs:

```
GET /api/customer-orders/by-user?nombre_usuario=Juan
GET /api/customer-orders/by-user?nombre_usuario=Juan&per_page=20
GET /api/customer-orders/by-user?nombre_usuario=Juan&per_page=50&page=1
```

---

## 📤 Respuesta Exitosa (200 OK)

### Estructura de Respuesta

```json
{
    "message": "Pedidos obtenidos exitosamente",
    "stats": {
        "total": 5,
        "pendientes": 2,
        "en_proceso": 1,
        "completados": 2
    },
    "orders": [
        {
            "pedido_id": 5,
            "numero_pedido": "PED-0005-20250115",
            "nombre": "Pedido Completo - Enero 2025",
            "estado": "pendiente",
            "estado_real": "pendiente",
            "fecha_creacion": "2025-01-15",
            "fecha_entrega": "2025-02-15",
            "descripcion": "Descripción completa del pedido",
            "observaciones": "Observaciones adicionales",
            "editable_hasta": "2025-12-31 23:59:59",
            "aprobado_en": null,
            "can_be_edited": true,
            "customer": {
                "cliente_id": 1,
                "razon_social": "Juan Pérez",
                "nombre_comercial": "Juan Pérez",
                "email": "cliente@example.com",
                "contacto": "Juan Pérez"
            },
            "orderProducts": [
                {
                    "producto_pedido_id": 10,
                    "producto_id": 1,
                    "cantidad": "100.50",
                    "precio": "2562.75",
                    "estado": "pendiente",
                    "observaciones": "Producto con especificaciones especiales",
                    "razon_rechazo": null,
                    "product": {
                        "producto_id": 1,
                        "codigo": "PROD-001",
                        "nombre": "Aceite de Oliva Extra Virgen",
                        "tipo": "organico",
                        "precio_unitario": "25.50",
                        "unit": {
                            "unidad_id": 1,
                            "codigo": "L",
                            "nombre": "Litro"
                        }
                    }
                }
            ],
            "destinations": [
                {
                    "destino_id": 8,
                    "direccion": "Av. Ejemplo 123, Santa Cruz, Bolivia",
                    "referencia": "Frente al parque central",
                    "latitud": "-17.8146",
                    "longitud": "-63.1561",
                    "nombre_contacto": "Juan Pérez",
                    "telefono_contacto": "+591 70000000",
                    "instrucciones_entrega": "Entregar en horario de oficina",
                    "destinationProducts": [
                        {
                            "producto_destino_id": 15,
                            "cantidad": "60.00",
                            "observaciones": "Cantidad para este destino",
                            "orderProduct": {
                                "producto_pedido_id": 10,
                                "producto_id": 1,
                                "cantidad": "100.50",
                                "product": {
                                    "producto_id": 1,
                                    "nombre": "Aceite de Oliva Extra Virgen",
                                    "codigo": "PROD-001"
                                }
                            }
                        }
                    ]
                }
            ],
            "batches": [
                {
                    "lote_id": 3,
                    "codigo_lote": "LOTE-0003-20250115",
                    "nombre": "Lote de Producción 1",
                    "fecha_creacion": "2025-01-15",
                    "hora_inicio": "2025-01-15 08:00:00",
                    "hora_fin": null,
                    "cantidad_objetivo": "100.50",
                    "cantidad_producida": "95.25",
                    "observaciones": "Lote en proceso",
                    "estado": "En Proceso",
                    "latestFinalEvaluation": null,
                    "has_storage": false,
                    "has_process_records": true
                }
            ]
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 5,
        "from": 1,
        "to": 5
    }
}
```

---

## 📊 Descripción de Campos de Respuesta

### Stats (Estadísticas)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total` | integer | Total de pedidos del usuario |
| `pendientes` | integer | Cantidad de pedidos pendientes |
| `en_proceso` | integer | Cantidad de pedidos en proceso |
| `completados` | integer | Cantidad de pedidos completados |

### Order (Pedido)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `pedido_id` | integer | ID único del pedido |
| `numero_pedido` | string | Número de pedido generado automáticamente |
| `nombre` | string | Nombre del pedido |
| `estado` | string | Estado del pedido: `pendiente`, `aprobado`, `rechazado`, `en_produccion`, `completado`, `cancelado` |
| `estado_real` | string | Estado real calculado basado en los lotes: `pendiente`, `en_proceso`, `completado`, `aprobado` |
| `fecha_creacion` | date | Fecha de creación del pedido |
| `fecha_entrega` | date | Fecha de entrega esperada |
| `descripcion` | string | Descripción del pedido |
| `observaciones` | string | Observaciones adicionales |
| `editable_hasta` | datetime | Fecha límite para editar el pedido |
| `aprobado_en` | datetime | Fecha de aprobación del pedido (si fue aprobado) |
| `can_be_edited` | boolean | Si el pedido puede ser editado |
| `customer` | object | Información del cliente |
| `orderProducts` | array | Array de productos del pedido |
| `destinations` | array | Array de destinos del pedido |
| `batches` | array | Array de lotes de producción asociados |

### OrderProduct (Producto del Pedido)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `producto_pedido_id` | integer | ID del producto en el pedido |
| `producto_id` | integer | ID del producto |
| `cantidad` | decimal | Cantidad del producto |
| `precio` | decimal | Precio total (precio_unitario × cantidad) |
| `estado` | string | Estado: `pendiente`, `aprobado`, `rechazado` |
| `observaciones` | string | Observaciones del producto |
| `razon_rechazo` | string | Razón de rechazo (si fue rechazado) |
| `product` | object | Información completa del producto |

### Destination (Destino)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `destino_id` | integer | ID del destino |
| `direccion` | string | Dirección completa |
| `referencia` | string | Referencia adicional |
| `latitud` | decimal | Latitud geográfica |
| `longitud` | decimal | Longitud geográfica |
| `nombre_contacto` | string | Nombre del contacto |
| `telefono_contacto` | string | Teléfono del contacto |
| `instrucciones_entrega` | string | Instrucciones de entrega |
| `destinationProducts` | array | Productos asignados a este destino |

### Batch (Lote de Producción)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `lote_id` | integer | ID del lote |
| `codigo_lote` | string | Código del lote |
| `nombre` | string | Nombre del lote |
| `fecha_creacion` | date | Fecha de creación |
| `hora_inicio` | datetime | Hora de inicio de producción |
| `hora_fin` | datetime | Hora de fin de producción |
| `cantidad_objetivo` | decimal | Cantidad objetivo |
| `cantidad_producida` | decimal | Cantidad producida |
| `observaciones` | string | Observaciones del lote |
| `estado` | string | Estado: `Pendiente`, `En Proceso`, `Certificado`, `No Certificado` |
| `latestFinalEvaluation` | object | Última evaluación final (si existe) |
| `has_storage` | boolean | Si el lote tiene almacenamiento |
| `has_process_records` | boolean | Si el lote tiene registros de proceso |

---

## 📊 Ejemplos de Uso

### 1. Obtener Todos los Pedidos de un Usuario

**Request:**
```
GET http://127.0.0.1:8001/api/customer-orders/by-user?nombre_usuario=Juan
```

**Headers:**
```
Accept: application/json
```

### 2. Obtener Pedidos con Más Resultados por Página

**Request:**
```
GET http://127.0.0.1:8001/api/customer-orders/by-user?nombre_usuario=Juan&per_page=50
```

### 3. Obtener Segunda Página de Pedidos

**Request:**
```
GET http://127.0.0.1:8001/api/customer-orders/by-user?nombre_usuario=Juan&page=2&per_page=20
```

---

## ❌ Respuestas de Error

### 400 Bad Request - Validación Fallida

```json
{
    "message": "Datos inválidos",
    "errors": {
        "nombre_usuario": [
            "The nombre usuario field is required."
        ]
    }
}
```

### 200 OK - Sin Pedidos Encontrados

```json
{
    "message": "No se encontraron pedidos para este nombre de usuario",
    "orders": [],
    "stats": {
        "total": 0,
        "pendientes": 0,
        "en_proceso": 0,
        "completados": 0
    }
}
```

**Nota:** Si no se encuentran pedidos, retorna 200 OK con arrays vacíos, no un error.

### 500 Internal Server Error

```json
{
    "message": "Error al obtener pedidos",
    "error": "Mensaje de error detallado"
}
```

---

## 🧪 Pasos para Probar en Postman

### 1. Configurar la Request

1. **Método:** Selecciona `GET`
2. **URL:** `http://127.0.0.1:8001/api/customer-orders/by-user`
3. **Headers:**
   - `Accept: application/json`

### 2. Agregar Parámetros

1. Haz clic en la pestaña **Params**
2. Agrega el parámetro:
   - Key: `nombre_usuario`
   - Value: `Juan` (o el nombre que usaste al crear el pedido)

### 3. Parámetros Opcionales (Paginación)

Si quieres paginación, agrega:
- Key: `per_page`
- Value: `20` (o la cantidad que quieras)

- Key: `page`
- Value: `1` (número de página)

### 4. Enviar la Request

Haz clic en **Send** y revisa la respuesta.

---

## 📌 Notas Importantes

1. **Nombre de Usuario:**
   - Debe ser exactamente el mismo que usaste al crear el pedido
   - El sistema busca en los campos `contacto`, `razon_social` y `nombre_comercial` del cliente
   - La búsqueda es case-insensitive (no distingue mayúsculas/minúsculas)
   - Busca nombres que empiecen con el `nombre_usuario` proporcionado

2. **Estado Real:**
   - El campo `estado_real` se calcula automáticamente basándose en los lotes de producción
   - Puede ser diferente del campo `estado` del pedido
   - Estados posibles: `pendiente`, `en_proceso`, `completado`, `aprobado`

3. **Paginación:**
   - Por defecto muestra 15 pedidos por página
   - Usa `per_page` para cambiar la cantidad
   - Usa `page` para navegar entre páginas

4. **Lotes de Producción:**
   - Si el pedido tiene lotes asociados, se muestran en el array `batches`
   - Cada lote incluye su estado, evaluación final y registros de proceso

5. **Productos por Destino:**
   - Cada destino muestra los productos asignados en `destinationProducts`
   - Incluye la cantidad específica para ese destino

---

## 🔍 Cómo Funciona la Búsqueda

El sistema busca clientes donde el `nombre_usuario` coincida con:
- `contacto` (empieza con el nombre)
- `razon_social` (empieza con el nombre)
- `nombre_comercial` (empieza con el nombre)

**Ejemplo:**
- Si creaste un pedido con `nombre_usuario: "Juan"`
- El sistema guardó el cliente con `contacto: "Juan Pérez"`
- Al buscar con `nombre_usuario: "Juan"`, encontrará el cliente porque "Juan Pérez" empieza con "Juan"

---

## 📞 Ejemplo de Request en cURL

```bash
# Obtener todos los pedidos de un usuario
curl -X GET "http://127.0.0.1:8001/api/customer-orders/by-user?nombre_usuario=Juan" \
  -H "Accept: application/json"

# Con paginación
curl -X GET "http://127.0.0.1:8001/api/customer-orders/by-user?nombre_usuario=Juan&per_page=50&page=1" \
  -H "Accept: application/json"
```

---

## 💡 Casos de Uso

### Ver Todos Mis Pedidos

```
GET /api/customer-orders/by-user?nombre_usuario=Juan
```

### Ver Solo Pedidos Pendientes (filtrar en el cliente)

Después de obtener la respuesta, filtra por `estado_real: "pendiente"` en tu aplicación.

### Ver Detalle de un Pedido Específico

1. Obtén la lista de pedidos
2. Encuentra el `pedido_id` del pedido que quieres ver
3. Usa el endpoint de editar o crea uno para ver detalles: `GET /api/customer-orders/{id}` (si existe)

---

## 🔗 Endpoints Relacionados

- **Crear pedido:** `POST /api/customer-orders` (público, sin token)
- **Editar pedido:** `PUT /api/customer-orders/{id}/public` (público, sin token, requiere nombre_usuario)
- **Ver productos:** `GET /api/products` (público, sin token)

---

## ⚠️ Advertencias

1. **Nombre de Usuario Exacto:** El `nombre_usuario` debe ser el mismo que usaste al crear el pedido. Si no estás seguro, prueba con el nombre exacto que usaste.

2. **Múltiples Clientes:** Si hay múltiples clientes con el mismo nombre, se mostrarán los pedidos de todos ellos.

3. **Privacidad:** Aunque el endpoint es público, solo muestra pedidos del cliente que coincide con el `nombre_usuario` proporcionado.

---

¡Listo para usar! 🚀

