# 📦 Endpoint GET - Obtener Productos Disponibles

## 🔗 Información del Endpoint

**URL:** `GET /api/products`

**Base URL:** `http://127.0.0.1:8001` (o tu URL del servidor)

**URL Completa:** `http://127.0.0.1:8001/api/products`

---

## 🔐 Autenticación

Este endpoint **acepta token opcional**. Funciona de dos formas:

### Opción 1: Con Token (Usuario Autenticado)
- Si envías el header `Authorization: Bearer {token}`, el sistema reconoce al usuario autenticado
- **No hay diferencia funcional** - el endpoint funciona igual con o sin token

### Opción 2: Sin Token (Público)
- Si no envías token, el endpoint funciona normalmente
- Puedes obtener la lista de productos sin necesidad de autenticarte

### Cómo obtener el token (si lo necesitas para otros endpoints):

1. **Login:**
   ```
   POST /api/auth/login
   Body:
   {
       "username": "tu_usuario",
       "password": "tu_password"
   }
   ```

2. **Respuesta del login incluye:**
   ```json
   {
       "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
       "operator": {
           "operador_id": 1,
           "nombre": "Juan",
           "apellido": "Pérez",
           "usuario": "jperez",
           "email": "juan@example.com"
       }
   }
   ```

---

## 📋 Headers Requeridos

```
Accept: application/json
Authorization: Bearer {token}  (OPCIONAL)
```

**Nota:** El header `Authorization` es opcional. Si lo incluyes, se reconoce al usuario autenticado, pero el endpoint funciona igual sin él.

---

## 🔍 Parámetros de Query (Opcionales)

Puedes agregar estos parámetros a la URL para filtrar los resultados:

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `tipo` | string | Filtrar por tipo de producto. Valores: `organico`, `marca_univalle`, `comestibles` | `?tipo=organico` |
| `activo` | boolean | Filtrar por estado activo. Valores: `true`, `false`, `1`, `0` | `?activo=true` |
| `per_page` | integer | Cantidad de resultados por página (paginación). Por defecto: 15 | `?per_page=50` |
| `page` | integer | Número de página (para paginación). Por defecto: 1 | `?page=2` |

### Ejemplos de URLs con parámetros:

```
GET /api/products?activo=true
GET /api/products?tipo=organico&activo=true
GET /api/products?activo=true&per_page=50
GET /api/products?tipo=marca_univalle&activo=true&per_page=100&page=1
```

---

## 📤 Respuesta Exitosa (200 OK)

### Estructura de Respuesta (con paginación)

```json
{
    "current_page": 1,
    "data": [
        {
            "producto_id": 1,
            "codigo": "PROD-001",
            "nombre": "Aceite de Oliva Extra Virgen",
            "tipo": "organico",
            "peso": "500.00",
            "precio_unitario": "25.50",
            "unidad_id": 1,
            "descripcion": "Aceite de oliva extra virgen de primera calidad",
            "activo": true,
            "created_at": "2025-01-15T10:30:00.000000Z",
            "updated_at": "2025-01-15T10:30:00.000000Z",
            "unit": {
                "unidad_id": 1,
                "codigo": "L",
                "nombre": "Litro",
                "descripcion": "Unidad de medida en litros",
                "activo": true
            }
        },
        {
            "producto_id": 2,
            "codigo": "PROD-002",
            "nombre": "Miel de Abeja Natural",
            "tipo": "organico",
            "peso": "250.00",
            "precio_unitario": "15.75",
            "unidad_id": 2,
            "descripcion": "Miel de abeja 100% natural",
            "activo": true,
            "created_at": "2025-01-15T10:30:00.000000Z",
            "updated_at": "2025-01-15T10:30:00.000000Z",
            "unit": {
                "unidad_id": 2,
                "codigo": "KG",
                "nombre": "Kilogramo",
                "descripcion": "Unidad de medida en kilogramos",
                "activo": true
            }
        }
    ],
    "first_page_url": "http://127.0.0.1:8001/api/products?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://127.0.0.1:8001/api/products?page=3",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://127.0.0.1:8001/api/products?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": "http://127.0.0.1:8001/api/products?page=2",
            "label": "2",
            "active": false
        },
        {
            "url": "http://127.0.0.1:8001/api/products?page=3",
            "label": "3",
            "active": false
        },
        {
            "url": "http://127.0.0.1:8001/api/products?page=2",
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": "http://127.0.0.1:8001/api/products?page=2",
    "path": "http://127.0.0.1:8001/api/products",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 42
}
```

### Estructura de un Producto

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `producto_id` | integer | ID único del producto |
| `codigo` | string | Código único del producto (máx. 50 caracteres) |
| `nombre` | string | Nombre del producto (máx. 200 caracteres) |
| `tipo` | string | Tipo de producto: `organico`, `marca_univalle`, `comestibles` |
| `peso` | decimal | Peso del producto (2 decimales) |
| `precio_unitario` | decimal | Precio unitario del producto (2 decimales) |
| `unidad_id` | integer | ID de la unidad de medida |
| `descripcion` | string | Descripción del producto |
| `activo` | boolean | Si el producto está activo o no |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de última actualización |
| `unit` | object | Objeto con información de la unidad de medida (ver abajo) |

### Estructura de Unit (Unidad de Medida)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `unidad_id` | integer | ID único de la unidad |
| `codigo` | string | Código de la unidad (ej: "L", "KG", "GR") |
| `nombre` | string | Nombre completo de la unidad |
| `descripcion` | string | Descripción de la unidad |
| `activo` | boolean | Si la unidad está activa |

---

## 📊 Ejemplos de Uso

### 1. Obtener Todos los Productos (Paginado)

**Request:**
```
GET http://127.0.0.1:8001/api/products
```

**Headers:**
```
Authorization: Bearer {tu_token}
Accept: application/json
```

### 2. Obtener Solo Productos Activos

**Request:**
```
GET http://127.0.0.1:8001/api/products?activo=true
```

### 3. Obtener Productos Orgánicos Activos

**Request:**
```
GET http://127.0.0.1:8001/api/products?tipo=organico&activo=true
```

### 4. Obtener Productos con Más Resultados por Página

**Request:**
```
GET http://127.0.0.1:8001/api/products?activo=true&per_page=50
```

### 5. Obtener Segunda Página de Productos

**Request:**
```
GET http://127.0.0.1:8001/api/products?page=2&per_page=20
```

---

## ❌ Respuestas de Error

### 401 Unauthorized - Sin Autenticación

Este error **no debería aparecer** ya que el endpoint es público. Si aparece, puede ser un problema de configuración.

```json
{
    "message": "Unauthenticated."
}
```

### 500 Internal Server Error

```json
{
    "message": "Error al obtener productos",
    "error": "Mensaje de error detallado"
}
```

---

## 🧪 Pasos para Probar en Postman

### 1. Configurar la Request

1. **Método:** Selecciona `GET`
2. **URL:** `http://127.0.0.1:8001/api/products`
3. **Headers:**
   - `Accept: application/json`
   - `Authorization: Bearer {tu_token}` (OPCIONAL - puedes omitirlo)

### 2. Agregar Parámetros (Opcional)

1. Haz clic en la pestaña **Params**
2. Agrega los parámetros que necesites:
   - `activo` = `true`
   - `tipo` = `organico`
   - `per_page` = `50`

### 3. Enviar la Request

Haz clic en **Send** y revisa la respuesta.

---

## 📌 Notas Importantes

1. **Paginación:** Por defecto, se muestran 15 productos por página. Usa `per_page` para cambiar esto.

2. **Filtros:** Puedes combinar múltiples filtros en la misma petición:
   ```
   ?tipo=organico&activo=true&per_page=50
   ```

3. **Ordenamiento:** Los productos se ordenan automáticamente por nombre (alfabéticamente).

4. **Unidad de Medida:** Cada producto incluye su unidad de medida relacionada en el campo `unit`.

5. **Precio Unitario:** El campo `precio_unitario` se usa para calcular el precio total cuando se crea un pedido.

---

## 🔍 Tipos de Producto Disponibles

- `organico`: Productos orgánicos
- `marca_univalle`: Productos de marca Univalle
- `comestibles`: Productos comestibles

---

## 📞 Ejemplo de Request en cURL

### Sin Token (Público)

```bash
# Obtener todos los productos activos
curl -X GET "http://127.0.0.1:8001/api/products?activo=true" \
  -H "Accept: application/json"

# Obtener productos orgánicos activos
curl -X GET "http://127.0.0.1:8001/api/products?tipo=organico&activo=true" \
  -H "Accept: application/json"

# Obtener con paginación personalizada
curl -X GET "http://127.0.0.1:8001/api/products?activo=true&per_page=50&page=1" \
  -H "Accept: application/json"
```

### Con Token (Opcional)

```bash
# Obtener todos los productos activos con token
curl -X GET "http://127.0.0.1:8001/api/products?activo=true" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

---

## 🔗 Endpoints Relacionados

- **Obtener un producto específico:** `GET /api/products/{id}`
- **Crear producto:** `POST /api/products` (requiere permisos)
- **Actualizar producto:** `PUT /api/products/{id}` (requiere permisos)
- **Eliminar producto:** `DELETE /api/products/{id}` (requiere permisos)

---

## 💡 Casos de Uso

### Para Crear un Pedido

1. Primero, obtén la lista de productos disponibles:
   ```
   GET /api/products?activo=true
   ```

2. Selecciona los `producto_id` que necesites

3. Usa esos IDs en el endpoint de crear pedido:
   ```
   POST /api/customer-orders
   {
       "products": [
           {
               "producto_id": 1,  // ID obtenido del endpoint de productos
               "cantidad": 100.50
           }
       ],
       ...
   }
   ```

---

¡Listo para usar! 🚀

