# ✏️ Endpoint PUT - Editar Pedido (Público)

## 🔗 Información del Endpoint

**URL:** `PUT /api/customer-orders/{id}/public`

**Base URL:** `http://127.0.0.1:8001` (o tu URL del servidor)

**URL Completa:** `http://127.0.0.1:8001/api/customer-orders/{id}/public`

**Ejemplo:** `http://127.0.0.1:8001/api/customer-orders/5/public`

---

## 🔐 Autenticación

Este endpoint **NO requiere autenticación**. Es completamente público.

**Validación de Seguridad:** El único requisito es que el campo `nombre_usuario` del request coincida con el nombre del cliente que creó el pedido originalmente.

---

## 📋 Headers Requeridos

```
Content-Type: application/json
Accept: application/json
```

**Nota:** No se requiere el header `Authorization`.

---

## 🔑 Validación de Permisos

Para editar un pedido, el `nombre_usuario` que envíes en el body debe coincidir con el nombre del cliente que creó el pedido.

**Cómo funciona:**
- Cuando se crea un pedido, el sistema guarda el nombre del cliente en el campo `contacto` o `razon_social`
- Al editar, el sistema compara el `nombre_usuario` del request con el nombre guardado del cliente
- Si coinciden, permite la edición
- Si no coinciden, retorna error 403

---

## 📝 Body del Request (JSON)

### Estructura Completa

```json
{
    "nombre_usuario": "Juan",
    "nombre": "Pedido Actualizado - Enero 2025",
    "fecha_entrega": "2025-02-20",
    "descripcion": "Descripción actualizada del pedido",
    "observaciones": "Observaciones actualizadas",
    "editable_hasta": "2025-12-31 23:59:59",
    "products": [
        {
            "producto_id": 1,
            "cantidad": 150.50,
            "observaciones": "Cantidad actualizada"
        },
        {
            "producto_id": 2,
            "cantidad": 75.25,
            "observaciones": null
        }
    ],
    "destinations": [
        {
            "direccion": "Av. Actualizada 456, Santa Cruz, Bolivia",
            "latitud": -17.8146,
            "longitud": -63.1561,
            "referencia": "Nueva referencia",
            "nombre_contacto": "Juan Pérez",
            "telefono_contacto": "+591 70000000",
            "instrucciones_entrega": "Nuevas instrucciones de entrega",
            "products": [
                {
                    "order_product_index": 0,
                    "cantidad": 100.00,
                    "observaciones": "Cantidad actualizada para este destino"
                },
                {
                    "order_product_index": 1,
                    "cantidad": 50.00,
                    "observaciones": null
                }
            ]
        }
    ]
}
```

---

## 📊 Descripción de Campos

### Campos Requeridos

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `nombre_usuario` | string | ✅ **Sí** | Nombre del usuario que creó el pedido. Debe coincidir exactamente con el nombre del cliente del pedido |
| `nombre` | string | ✅ **Sí** | Nombre del pedido (máx. 200 caracteres) |
| `products` | array | ✅ **Sí** | Array de productos del pedido (mínimo 1) |
| `destinations` | array | ✅ **Sí** | Array de destinos del pedido (mínimo 1) |

### Campos Opcionales

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `fecha_entrega` | date | Fecha de entrega esperada (formato: YYYY-MM-DD) |
| `descripcion` | string | Descripción general del pedido |
| `observaciones` | string | Observaciones adicionales del pedido |
| `editable_hasta` | datetime | Fecha límite para editar el pedido (formato: YYYY-MM-DD HH:MM:SS). Si no se envía, mantiene la fecha actual |

### Array `products` (Productos del Pedido)

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `producto_id` | integer | ✅ **Sí** | ID del producto. Debe existir en la tabla `producto` |
| `cantidad` | numeric | ✅ **Sí** | Cantidad del producto (mínimo: 0.0001) |
| `observaciones` | string | ❌ No | Observaciones específicas para este producto |

**Nota:** Debe haber al menos 1 producto en el array.

### Array `destinations` (Destinos del Pedido)

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `direccion` | string | ✅ **Sí** | Dirección completa del destino (máx. 500 caracteres) |
| `latitud` | numeric | ❌ No | Latitud geográfica (rango: -90 a 90) |
| `longitud` | numeric | ❌ No | Longitud geográfica (rango: -180 a 180) |
| `referencia` | string | ❌ No | Referencia adicional de la ubicación (máx. 200 caracteres) |
| `nombre_contacto` | string | ❌ No | Nombre de la persona de contacto (máx. 200 caracteres) |
| `telefono_contacto` | string | ❌ No | Teléfono de contacto (máx. 20 caracteres) |
| `instrucciones_entrega` | string | ❌ No | Instrucciones especiales para la entrega |
| `products` | array | ✅ **Sí** | Array de productos asignados a este destino (ver abajo) |

**Nota:** Debe haber al menos 1 destino en el array.

### Array `destinations[].products` (Productos por Destino)

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `order_product_index` | integer | ✅ **Sí** | Índice del producto en el array `products` del pedido (empieza en 0) |
| `cantidad` | numeric | ✅ **Sí** | Cantidad de este producto para este destino (mínimo: 0.0001) |
| `observaciones` | string | ❌ No | Observaciones específicas para este producto en este destino |

---

## ✅ Ejemplo Mínimo (Solo Campos Requeridos)

```json
{
    "nombre_usuario": "Juan",
    "nombre": "Pedido Actualizado",
    "products": [
        {
            "producto_id": 1,
            "cantidad": 100
        }
    ],
    "destinations": [
        {
            "direccion": "Av. Principal 123, Santa Cruz, Bolivia",
            "products": [
                {
                    "order_product_index": 0,
                    "cantidad": 100
                }
            ]
        }
    ]
}
```

---

## 📤 Respuesta Exitosa (200 OK)

```json
{
    "message": "Pedido actualizado exitosamente",
    "order": {
        "pedido_id": 5,
        "cliente_id": 1,
        "numero_pedido": "PED-0005-20250115",
        "nombre": "Pedido Actualizado - Enero 2025",
        "estado": "pendiente",
        "fecha_creacion": "2025-01-15",
        "fecha_entrega": "2025-02-20",
        "descripcion": "Descripción actualizada del pedido",
        "observaciones": "Observaciones actualizadas",
        "editable_hasta": "2025-12-31 23:59:59",
        "orderProducts": [
            {
                "producto_pedido_id": 15,
                "pedido_id": 5,
                "producto_id": 1,
                "cantidad": "150.50",
                "precio": "3842.75",
                "estado": "pendiente",
                "observaciones": "Cantidad actualizada",
                "product": {
                    "producto_id": 1,
                    "codigo": "PROD-001",
                    "nombre": "Aceite de Oliva",
                    "precio_unitario": "25.50"
                }
            }
        ],
        "destinations": [
            {
                "destino_id": 10,
                "pedido_id": 5,
                "direccion": "Av. Actualizada 456, Santa Cruz, Bolivia",
                "latitud": "-17.8146",
                "longitud": "-63.1561",
                "referencia": "Nueva referencia",
                "nombre_contacto": "Juan Pérez",
                "telefono_contacto": "+591 70000000",
                "instrucciones_entrega": "Nuevas instrucciones de entrega"
            }
        ]
    }
}
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
        ],
        "nombre": [
            "The nombre field is required."
        ],
        "products.0.producto_id": [
            "The selected products.0.producto id is invalid."
        ]
    }
}
```

### 403 Forbidden - Nombre de Usuario No Coincide

```json
{
    "message": "No tienes permiso para editar este pedido. El nombre de usuario no coincide con el cliente del pedido."
}
```

### 403 Forbidden - Pedido No Editable

```json
{
    "message": "El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición."
}
```

### 404 Not Found - Pedido No Encontrado

```json
{
    "message": "No query results for model [App\\Models\\CustomerOrder] {id}"
}
```

### 500 Internal Server Error

```json
{
    "message": "Error al actualizar pedido",
    "error": "Mensaje de error detallado"
}
```

---

## 🧪 Pasos para Probar en Postman

### 1. Obtener el ID del Pedido

Primero necesitas el ID del pedido que quieres editar. Puedes obtenerlo:
- Del número de pedido que recibiste al crear el pedido
- O consultando el pedido si tienes acceso

### 2. Obtener el Nombre de Usuario Correcto

El `nombre_usuario` debe ser exactamente el mismo que usaste al crear el pedido. Por ejemplo:
- Si creaste el pedido con `"nombre_usuario": "Juan"`, debes usar el mismo "Juan" para editarlo

### 3. Configurar la Request

1. **Método:** Selecciona `PUT`
2. **URL:** `http://127.0.0.1:8001/api/customer-orders/{id}/public`
   - Reemplaza `{id}` con el ID del pedido (ej: `5`)
   - Ejemplo: `http://127.0.0.1:8001/api/customer-orders/5/public`
3. **Headers:**
   - `Content-Type: application/json`
   - `Accept: application/json`

### 4. Configurar el Body

1. Selecciona la pestaña **Body**
2. Selecciona **raw**
3. Selecciona **JSON** en el dropdown
4. Pega el JSON con todos los campos que quieres actualizar

### 5. Enviar la Request

Haz clic en **Send** y revisa la respuesta.

---

## 📌 Notas Importantes

1. **Validación de Nombre de Usuario:**
   - El `nombre_usuario` debe coincidir exactamente con el nombre del cliente que creó el pedido
   - El sistema compara el `nombre_usuario` con el campo `contacto` o `razon_social` del cliente
   - La comparación es case-insensitive (no distingue mayúsculas/minúsculas)
   - El `nombre_usuario` debe estar al inicio del nombre del cliente

2. **Reemplazo Completo:**
   - Al editar, se **eliminan todos los productos y destinos existentes**
   - Se crean nuevos productos y destinos con los datos del request
   - Si quieres mantener algo, debes incluirlo en el request

3. **Estado del Pedido:**
   - Solo se pueden editar pedidos con estado `pendiente`
   - No se pueden editar pedidos que ya fueron aprobados o rechazados
   - No se pueden editar pedidos que expiraron el tiempo de edición

4. **Cálculo de Precios:**
   - Los precios se calculan automáticamente basándose en el `precio_unitario` del producto
   - Precio total = precio_unitario × cantidad

5. **Índices de Productos:**
   - El `order_product_index` en los destinos debe corresponder al índice del array `products` (empieza en 0)

6. **Transacciones:**
   - Si algo falla, toda la operación se revierte (rollback)
   - No se guardan cambios parciales

---

## 🔍 Validaciones Adicionales

- `nombre_usuario` es obligatorio y debe coincidir con el cliente del pedido
- `producto_id` debe existir en la tabla `producto`
- `cantidad` debe ser mayor a 0.0001
- `latitud` debe estar entre -90 y 90
- `longitud` debe estar entre -180 y 180
- `editable_hasta` debe ser una fecha futura o igual a ahora (si se proporciona)
- Debe haber al menos 1 producto
- Debe haber al menos 1 destino
- Cada destino debe tener al menos 1 producto asignado

---

## 📞 Ejemplo de Request en cURL

```bash
curl -X PUT "http://127.0.0.1:8001/api/customer-orders/5/public" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nombre_usuario": "Juan",
    "nombre": "Pedido Actualizado",
    "fecha_entrega": "2025-02-20",
    "products": [
        {
            "producto_id": 1,
            "cantidad": 150.50
        }
    ],
    "destinations": [
        {
            "direccion": "Av. Actualizada 456, Santa Cruz, Bolivia",
            "products": [
                {
                    "order_product_index": 0,
                    "cantidad": 150.50
                }
            ]
        }
    ]
}'
```

---

## 💡 Casos de Uso

### Escenario 1: Actualizar Cantidades de Productos

```json
{
    "nombre_usuario": "Juan",
    "nombre": "Pedido Actualizado",
    "products": [
        {
            "producto_id": 1,
            "cantidad": 200.00
        }
    ],
    "destinations": [
        {
            "direccion": "Av. Principal 123",
            "products": [
                {
                    "order_product_index": 0,
                    "cantidad": 200.00
                }
            ]
        }
    ]
}
```

### Escenario 2: Agregar Nuevos Productos

```json
{
    "nombre_usuario": "Juan",
    "nombre": "Pedido Actualizado",
    "products": [
        {
            "producto_id": 1,
            "cantidad": 100.00
        },
        {
            "producto_id": 3,
            "cantidad": 50.00
        }
    ],
    "destinations": [
        {
            "direccion": "Av. Principal 123",
            "products": [
                {
                    "order_product_index": 0,
                    "cantidad": 60.00
                },
                {
                    "order_product_index": 1,
                    "cantidad": 50.00
                }
            ]
        }
    ]
}
```

### Escenario 3: Cambiar Destinos

```json
{
    "nombre_usuario": "Juan",
    "nombre": "Pedido Actualizado",
    "products": [
        {
            "producto_id": 1,
            "cantidad": 100.00
        }
    ],
    "destinations": [
        {
            "direccion": "Nueva Dirección 789, La Paz, Bolivia",
            "latitud": -16.5000,
            "longitud": -68.1500,
            "nombre_contacto": "María González",
            "telefono_contacto": "+591 70111111",
            "products": [
                {
                    "order_product_index": 0,
                    "cantidad": 100.00
                }
            ]
        }
    ]
}
```

---

## ⚠️ Advertencias

1. **Reemplazo Total:** Todos los productos y destinos anteriores se eliminan y se reemplazan por los nuevos.

2. **Nombre de Usuario Exacto:** El `nombre_usuario` debe ser exactamente el mismo que usaste al crear el pedido. Si no estás seguro, verifica el pedido primero.

3. **Estado del Pedido:** Solo puedes editar pedidos en estado `pendiente`. Una vez aprobado, no se puede editar.

4. **Tiempo de Edición:** Si el pedido expiró el tiempo de edición (`editable_hasta`), no se puede editar.

---

## 🔗 Endpoints Relacionados

- **Crear pedido:** `POST /api/customer-orders` (público, sin token)
- **Ver pedido:** `GET /api/customer-orders/{id}` (requiere token)
- **Editar pedido (con token):** `PUT /api/customer-orders/{id}` (requiere token)
- **Listar productos:** `GET /api/products` (público, sin token)

---

¡Listo para usar! 🚀

