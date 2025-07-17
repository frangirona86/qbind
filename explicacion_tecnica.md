# 🇮🇹 Sistema de Validación de Números VAT Italianos

## 📋 Resumen del Proyecto

Este es un **sistema web en PHP** que valida números VAT (IVA) italianos utilizando **programación orientada a objetos**. El sistema puede procesar archivos CSV con múltiples números VAT o validar números individuales, almacenando todos los resultados en una base de datos MySQL.

## 🏗️ Arquitectura del Sistema

### **Patrón MVC (Model-View-Controller)**

```
├── index.php              # Punto de entrada principal
├── controllers/
│   └── VATController.php   # Controlador principal (maneja requests HTTP)
├── includes/
│   ├── VATProcessor.php    # Modelo (lógica de negocio)
│   └── VATValidator.php    # Clase de validación
├── config/
│   └── database.php        # Configuración de base de datos
├── Views/
│   ├── header.php          # Plantillas de vista
│   └── footer.php
├── database/
│   └── setup.sql           # Esquema de base de datos
└── uploads/                # Directorio para archivos CSV
```

## 🔧 Componentes Principales

### 1. **VATController.php** - El Controlador
**Responsabilidad**: Manejar las peticiones HTTP y coordinar las respuestas

```php
class VATController {
    private $processor;
    private $uploadDir = 'uploads/';
    
    // Maneja la subida y procesamiento de archivos CSV
    public function handleCSVUpload($file)
    
    // Maneja la validación de un VAT individual
    public function handleSingleVATValidation($vatNumber)
}
```

**Características importantes**:
- ✅ Gestión de errores de subida de archivos
- ✅ Validación de tipos de archivo
- ✅ Limpieza automática de archivos temporales
- ✅ Redirección después del procesamiento exitoso

### 2. **VATValidator.php** - La Lógica de Validación
**Responsabilidad**: Implementar las reglas de validación específicas para VAT italianos

```php
class VATValidator {
    public function validateItalianVAT($vatNumber) {
        // 1. Detecta números de 11 dígitos sin prefijo "IT"
        // 2. Limpia el formato del número
        // 3. Valida el formato italiano (IT + 11 dígitos)
        // 4. Retorna resultado estructurado
    }
}
```

**Reglas de Validación**:
- ✅ Debe empezar con "IT"
- ✅ Debe tener exactamente 11 dígitos después de "IT"
- ✅ **Auto-corrección**: Si encuentra 11 dígitos sin "IT", los corrige automáticamente
- ✅ Limpieza de caracteres especiales (espacios, puntos, guiones)

### 3. **VATProcessor.php** - El Procesador de Datos
**Responsabilidad**: Coordinar validación y persistencia de datos

```php
class VATProcessor {
    private $db;           // Conexión a base de datos
    private $validator;    // Instancia del validador
    
    // Procesa archivos CSV completos
    public function processCSVFile($filePath)
    
    // Valida VAT individuales
    public function validateSingleVAT($vatNumber)
    
    // Guarda resultados en base de datos
    private function saveToDatabase($validation)
}
```

**Funcionalidades clave**:
- ✅ Validación de formato CSV (debe tener columnas 'id' y 'vat_number')
- ✅ Procesamiento línea por línea con manejo de errores
- ✅ Clasificación automática en 3 categorías
- ✅ Persistencia en base de datos con timestamps

## 📊 Base de Datos

### Esquema de la tabla `vat_validations`

```sql
CREATE TABLE vat_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_vat VARCHAR(20) NOT NULL,      -- VAT original sin modificar
    cleaned_vat VARCHAR(20) NOT NULL,       -- VAT después de limpieza
    corrected_vat VARCHAR(20) NULL,         -- VAT corregido (si aplica)
    status ENUM('acceptable', 'corrected', 'incorrect'),  -- Estado final
    message TEXT NOT NULL,                  -- Mensaje descriptivo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Índices para optimización**:
- `idx_status` - Para filtrar por estado
- `idx_created_at` - Para consultas por fecha
- `idx_original_vat` - Para búsquedas específicas

## 🎯 Flujo de Funcionamiento

### **Procesamiento de Archivo CSV**:

1. **Upload** → El usuario sube un archivo CSV
2. **Validación** → Se verifica formato del archivo y estructura
3. **Procesamiento** → Línea por línea:
   - Se extrae el VAT number
   - Se valida según reglas italianas
   - Se clasifica como: aceptable/corregido/incorrecto
   - Se guarda en base de datos
4. **Resultados** → Se almacenan en sesión y se redirige a página de resultados

### **Validación Individual**:

1. **Input** → Usuario ingresa un VAT number
2. **Validación** → Se aplican las mismas reglas
3. **Respuesta** → Se muestra resultado inmediato en la misma página

## 📈 Estados de Validación

### ✅ **ACCEPTABLE** (Aceptable)
- VAT number con formato correcto: "IT" + 11 dígitos
- Ejemplo: `IT12345678901`

### ⚠️ **CORRECTED** (Corregido)
- VAT number que se pudo corregir automáticamente
- Casos: 11 dígitos sin prefijo "IT"
- Ejemplo: `12345678901` → `IT12345678901`

### ❌ **INCORRECT** (Incorrecto)  
- VAT number que no se puede validar ni corregir
- Ejemplos: `IT123` (muy corto), `123-hello` (caracteres inválidos)

## 🛡️ Características de Seguridad y Robustez

### **Manejo de Errores**:
- ✅ Validación de tipos de archivo en upload
- ✅ Try-catch para operaciones de base de datos
- ✅ Verificación de permisos de directorio
- ✅ Limpieza de archivos temporales en caso de error

### **Validación de Datos**:
- ✅ Sanitización de inputs del usuario
- ✅ Uso de prepared statements (previene SQL injection)
- ✅ Validación de estructura CSV antes del procesamiento

### **Optimización**:
- ✅ Conexión única a base de datos por sesión
- ✅ Índices en base de datos para consultas rápidas
- ✅ Procesamiento en lotes para archivos grandes

## 🔄 Tecnologías Utilizadas

- **Backend**: PHP 7.4+ con POO
- **Base de Datos**: MySQL 5.7+ con PDO
- **Frontend**: HTML5, Bootstrap 5, JavaScript
- **Servidor**: Apache (XAMPP)
- **Arquitectura**: MVC Pattern

## 🚀 Puntos Fuertes para la Entrevista

1. **Arquitectura Limpia**: Separación clara de responsabilidades (MVC)
2. **Programación Orientada a Objetos**: Uso apropiado de clases y encapsulación
3. **Manejo de Errores**: Implementación robusta con try-catch y validaciones
4. **Base de Datos**: Diseño normalizado con índices para performance
5. **Seguridad**: Prepared statements, validación de inputs, sanitización
6. **Escalabilidad**: Código modular que permite fácil extensión
7. **UX/UI**: Interfaz responsive con feedback claro al usuario

## 💡 Posibles Mejoras (para discutir en entrevista)

1. **Implementar cache** para validaciones repetidas
2. **API REST** para integración con otros sistemas
3. **Validación de checksum** según algoritmo oficial italiano
4. **Sistema de logs** más detallado
5. **Tests unitarios** con PHPUnit
6. **Dockerización** para deployment
7. **Rate limiting** para prevenir abuse

## 🎯 Preguntas Técnicas Probables

**P: ¿Por qué usaste PDO en lugar de mysqli?**
**R**: PDO ofrece una interfaz más consistente, soporte para múltiples bases de datos, y mejor protección contra SQL injection con prepared statements.

**P: ¿Cómo manejas archivos CSV muy grandes?**
**R**: Procesamiento línea por línea con fopen/fgetcsv para no cargar todo en memoria. Para archivos enormes, implementaría procesamiento en background con colas.

**P: ¿Qué pasa si el servidor se cae durante el procesamiento?**
**R**: Actualmente se perdería el progreso. Para producción, implementaría transacciones de base de datos y un sistema de jobs con estado persistente.

Este sistema demuestra conocimientos sólidos en PHP, diseño de bases de datos, arquitectura MVC, y mejores prácticas de desarrollo web.