# Migración de Base de Datos a Español - Sin Bucles

## Resumen de Cambios

Esta migración refactoriza completamente la base de datos para:
1. **Traducir todos los nombres al español**
2. **Eliminar bucles/ciclos innecesarios**
3. **Eliminar redundancias**
4. **Mantener solo lo esencial**

## Cambios Principales

### 1. Eliminación de Redundancias

#### Tabla `status` - ELIMINADA
- **Razón**: Los estados se manejan directamente en las tablas que los necesitan (ej: `pedido_cliente.estado`)
- **Impacto**: Los estados ahora son campos directos en lugar de referencias a una tabla separada

#### Tabla `operator_role` - ELIMINADA
- **Razón**: Spatie Laravel Permission ya maneja los roles (`roles` y `permissions`)
- **Impacto**: El campo `role_id` se eliminó de `operador`. Los roles se gestionan completamente con Spatie

### 2. Eliminación de Bucles

#### Foreign Keys Eliminadas para Evitar Bucles:

1. **`pedido_cliente.aprobado_por`** → `operador.operador_id`
   - **Razón**: No es necesario mantener esta relación directa
   - **Solución**: Se puede obtener el operador mediante consulta directa si es necesario

2. **`producto_pedido.aprobado_por`** → `operador.operador_id`
   - **Razón**: Similar al anterior, evita bucles innecesarios

3. **`registro_movimiento_material.operador_id`** → `operador.operador_id`
   - **Razón**: Se mantiene el campo pero sin foreign key para evitar bucles

4. **`evaluacion_final_proceso.inspector_id`** → `operador.operador_id`
   - **Razón**: Similar, se mantiene el campo pero sin foreign key

#### Foreign Keys Mantenidas (Necesarias):

- `registro_proceso_maquina.operador_id` → `operador.operador_id` ✅
  - **Razón**: Es necesario para trazabilidad del proceso

### 3. Traducción de Nombres

| Tabla Antigua (Inglés) | Tabla Nueva (Español) | Cambios Clave |
|------------------------|----------------------|---------------|
| `unit_of_measure` | `unidad_medida` | `unit_id` → `unidad_id`, `code` → `codigo`, `name` → `nombre`, `active` → `activo` |
| `movement_type` | `tipo_movimiento` | `movement_type_id` → `tipo_movimiento_id`, `affects_stock` → `afecta_stock`, `is_entry` → `es_entrada` |
| `customer` | `cliente` | `customer_id` → `cliente_id`, `business_name` → `razon_social`, `trading_name` → `nombre_comercial`, `tax_id` → `nit` |
| `raw_material_category` | `categoria_materia_prima` | `category_id` → `categoria_id` |
| `supplier` | `proveedor` | `supplier_id` → `proveedor_id` |
| `standard_variable` | `variable_estandar` | `variable_id` → `variable_id` |
| `machine` | `maquina` | `machine_id` → `maquina_id` |
| `process` | `proceso` | `process_id` → `proceso_id` |
| `operator` | `operador` | `operator_id` → `operador_id`, `first_name` → `nombre`, `last_name` → `apellido`, `username` → `usuario`, **eliminado `role_id`** |
| `raw_material_base` | `materia_prima_base` | `material_id` → `material_id`, `available_quantity` → `cantidad_disponible` |
| `raw_material` | `materia_prima` | `raw_material_id` → `materia_prima_id`, `supplier_batch` → `lote_proveedor`, `receipt_date` → `fecha_recepcion` |
| `product` | `producto` | `product_id` → `producto_id` |
| `customer_order` | `pedido_cliente` | `order_id` → `pedido_id`, `order_number` → `numero_pedido`, `creation_date` → `fecha_creacion` |
| `order_product` | `producto_pedido` | `order_product_id` → `producto_pedido_id` |
| `order_destination` | `destino_pedido` | `destination_id` → `destino_id`, `address` → `direccion`, `contact_name` → `nombre_contacto` |
| `order_destination_product` | `producto_destino_pedido` | `destination_product_id` → `producto_destino_id` |
| `production_batch` | `lote_produccion` | `batch_id` → `lote_id`, `batch_code` → `codigo_lote` |
| `batch_raw_material` | `lote_materia_prima` | `batch_material_id` → `lote_material_id` |
| `material_movement_log` | `registro_movimiento_material` | `log_id` → `registro_id`, `movement_date` → `fecha_movimiento` |
| `process_machine` | `proceso_maquina` | `process_machine_id` → `proceso_maquina_id` |
| `process_machine_variable` | `variable_proceso_maquina` | `variable_id` → `variable_id` |
| `process_machine_record` | `registro_proceso_maquina` | `record_id` → `registro_id` |
| `process_final_evaluation` | `evaluacion_final_proceso` | `evaluation_id` → `evaluacion_id` |
| `storage` | `almacenaje` | `storage_id` → `almacenaje_id`, `pickup_latitude` → `latitud_recojo`, `pickup_longitude` → `longitud_recojo` |
| `material_request` | `solicitud_material` | `request_id` → `solicitud_id` |
| `material_request_detail` | `detalle_solicitud_material` | `detail_id` → `detalle_id` |
| `supplier_response` | `respuesta_proveedor` | `response_id` → `respuesta_id` |
| `order_envio_tracking` | `seguimiento_envio_pedido` | `envio_codigo` → `codigo_envio`, `error_message` → `mensaje_error` |

## Estructura de Relaciones (Sin Bucles)

```
unidad_medida (independiente)
tipo_movimiento (independiente)
cliente (independiente)
categoria_materia_prima (independiente)
proveedor (independiente)
variable_estandar (independiente)
maquina (independiente)
proceso (independiente)
operador (independiente) ← Sin role_id, Spatie maneja roles

materia_prima_base → categoria_materia_prima, unidad_medida
materia_prima → materia_prima_base, proveedor
producto → unidad_medida

pedido_cliente → cliente
producto_pedido → pedido_cliente, producto
destino_pedido → pedido_cliente
producto_destino_pedido → destino_pedido, producto_pedido

lote_produccion → pedido_cliente
lote_materia_prima → lote_produccion, materia_prima
registro_movimiento_material → materia_prima_base, tipo_movimiento (sin FK a operador)

proceso_maquina → proceso, maquina
variable_proceso_maquina → proceso_maquina, variable_estandar
registro_proceso_maquina → lote_produccion, proceso_maquina, operador ✅
evaluacion_final_proceso → lote_produccion (sin FK a operador)

almacenaje → lote_produccion

solicitud_material → pedido_cliente
detalle_solicitud_material → solicitud_material, materia_prima_base
respuesta_proveedor → solicitud_material, proveedor

seguimiento_envio_pedido (sin FKs, solo índices)
```

## Pasos para Aplicar la Migración

1. **Hacer backup de la base de datos**
   ```bash
   pg_dump -U usuario -d nombre_db > backup_antes_migracion.sql
   ```

2. **Ejecutar las migraciones**
   ```bash
   php artisan migrate
   ```

3. **Verificar que los datos se migraron correctamente**
   ```bash
   php artisan tinker
   # Verificar que las tablas nuevas tienen datos
   DB::table('pedido_cliente')->count();
   ```

4. **Actualizar modelos Eloquent** (próximo paso)

5. **Actualizar controladores y vistas** (próximo paso)

6. **Eliminar tablas antiguas** (después de verificar que todo funciona)

## Notas Importantes

- **Spatie**: Las tablas de Spatie (`roles`, `permissions`, `model_has_roles`, etc.) NO se tocan
- **Cache**: Se debe limpiar el cache después de la migración:
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  ```
- **Foreign Keys Eliminadas**: Algunas relaciones se mantienen como campos pero sin foreign keys para evitar bucles. Esto permite flexibilidad y evita problemas de dependencias circulares.

## Próximos Pasos

1. ✅ Crear migración de estructura en español
2. ✅ Crear migración de datos
3. ⏳ Actualizar modelos Eloquent
4. ⏳ Actualizar controladores
5. ⏳ Actualizar vistas (Blade)
6. ⏳ Actualizar seeders
7. ⏳ Crear migración para eliminar tablas antiguas (después de verificar)

