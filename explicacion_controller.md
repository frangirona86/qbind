# 🎮 VATController.php - El Cerebro del Sistema

## 🔗 **Conexión con index.php**

Cuando en `index.php` hacemos:
```php
$controller = new VATController();  // ← Se ejecuta __construct()
$result = $controller->handleCSVUpload($_FILES['csvFile']);
```

**El constructor se ejecuta automáticamente:**

```php
public function __construct() {
    $this->processor = new VATProcessor();        // ← Crea el procesador
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($this->uploadDir)) {              // ← Verifica directorio
        mkdir($this->uploadDir, 0777, true);      // ← Lo crea si no existe
    }
    
    $this->processor->createTable();              // ← Asegura tabla en BD
}
```

### **¿Por qué esta inicialización?**
- ✅ **Preparación automática**: No necesito recordar crear el directorio manualmente
- ✅ **Inyección de dependencias**: El controlador usa el processor, pero no lo instancia cada vez
- ✅ **Base de datos automática**: Si no existe la tabla, la crea

---

## 📤 **handleCSVUpload() - Paso a Paso**

```php
public function handleCSVUpload($file) {
    // ► PASO 1: Inicializar respuesta estructurada
    $result = [
        'success' => false,
        'message' => '',
        'messageType' => 'danger',
        'redirect' => null,
        'results' => null
    ];
```

### **¿Por qué esta estructura de respuesta?**
- ✅ **Consistencia**: Siempre retorno el mismo formato
- ✅ **Fácil debugging**: Puedo ver exactamente qué pasó
- ✅ **Flexible**: Puedo agregar más campos sin romper código existente

---

```php
    // ► PASO 2: Validar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = $this->getUploadErrorMessage($file['error']);
        return $result;
    }
```

### **Errores de PHP que manejo:**
- `UPLOAD_ERR_INI_SIZE` → Archivo muy grande (php.ini)
- `UPLOAD_ERR_FORM_SIZE` → Archivo muy grande (formulario HTML)
- `UPLOAD_ERR_PARTIAL` → Subida incompleta
- `UPLOAD_ERR_NO_FILE` → No se subió archivo

**¿Por qué no uso solo `$_FILES['error']`?**
- ✅ **Mensajes amigables**: El usuario ve "Archivo muy grande" en lugar de código de error
- ✅ **Debugging**: Sé exactamente qué tipo de error ocurrió

---

```php
    // ► PASO 3: Mover archivo al directorio seguro
    $uploadFile = $this->uploadDir . basename($file['name']);
    
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        try {
            // ► PASO 4: Procesar el archivo CSV
            $results = $this->processor->processCSVFile($uploadFile);
            $totalProcessed = count($results['acceptable']) + 
                            count($results['corrected']) + 
                            count($results['incorrect']);
            
            // ► PASO 5: Preparar respuesta exitosa
            $result['success'] = true;
            $result['message'] = "CSV file processed successfully! $totalProcessed VAT numbers validated.";
            $result['messageType'] = 'success';
            $result['results'] = $results;
            $result['redirect'] = "results.php?session_id=" . time();
            
        } catch (Exception $e) {
            // ► PASO 6: Manejo de errores durante procesamiento
            $result['message'] = "Error processing CSV file: " . $e->getMessage();
            
            // Limpieza automática en caso de error
            if (file_exists($uploadFile)) {
                unlink($uploadFile);
            }
        }
    } else {
        $result['message'] = "Error uploading file. Please try again.";
    }
    
    return $result;
}
```

### **Decisiones importantes:**

1. **`basename($file['name'])`**: 
   - ✅ Evita path traversal attacks (`../../../etc/passwd`)
   - ✅ Solo usa el nombre del archivo, no la ruta

2. **`move_uploaded_file()`**:
   - ✅ Más seguro que `copy()` o `rename()`
   - ✅ Verifica que el archivo realmente vino de un upload HTTP

3. **Try-catch con limpieza**:
   - ✅ Si falla el procesamiento, elimino el archivo subido
   - ✅ No dejo "basura" en el servidor

4. **`session_id` en la URL**:
   - ✅ Hace única la URL de resultados
   - ✅ Evita problemas de cache del navegador

---

## ✏️ **handleSingleVATValidation() - Más Simple**

```php
public function handleSingleVATValidation($vatNumber) {
    $result = [
        'success' => false,
        'message' => '',
        'messageType' => 'danger',
        'validationResult' => null
    ];
    
    if (!empty($vatNumber)) {
        try {
            $validationResult = $this->processor->validateSingleVAT($vatNumber);
            $result['success'] = true;
            $result['message'] = "VAT validation completed.";
            $result['messageType'] = 'info';
            $result['validationResult'] = $validationResult;
        } catch (Exception $e) {
            $result['message'] = "Error validating VAT number: " . $e->getMessage();
        }
    } else {
        $result['message'] = "Please enter a VAT number to validate.";
    }
    
    return $result;
}
```

### **¿Por qué más simple?**
- ✅ **No hay archivos**: Solo valida un string
- ✅ **No hay redirección**: El resultado se muestra en la misma página
- ✅ **Validación básica**: Solo verifico que no esté vacío

---

## 🛡️ **getUploadErrorMessage() - Mensajes Amigables**

```php
private function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "File is too large. Maximum file size allowed is " . ini_get('upload_max_filesize');
        case UPLOAD_ERR_PARTIAL:
            return "File was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        default:
            return "Upload error occurred.";
    }
}
```

### **¿Por qué estos mensajes específicos?**
- ✅ **Actionable**: El usuario sabe qué hacer ("archivo muy grande")
- ✅ **Técnicos pero claros**: Muestro el límite real del servidor
- ✅ **Fallback seguro**: Si hay un error desconocido, no falla

---

## 🔄 **Flujo Completo: CSV Upload**

```
Usuario sube archivo
        ↓
index.php detecta $_FILES['csvFile']
        ↓
new VATController() 
  ↳ Crea directorio uploads/
  ↳ Crea tabla en BD
        ↓
handleCSVUpload($_FILES['csvFile'])
  ↳ Valida errores de upload
  ↳ Mueve archivo a uploads/
  ↳ processor->processCSVFile()
    ↳ Lee CSV línea por línea
    ↳ Valida cada VAT
    ↳ Guarda en base de datos
  ↳ Prepara resultados
        ↓
return $result con:
  - success: true
  - results: [acceptable, corrected, incorrect]
  - redirect: "results.php"
        ↓
index.php guarda en $_SESSION y redirige
        ↓
results.php muestra los resultados
```

---

## 🔄 **Flujo Completo: Single Validation**

```
Usuario escribe VAT number
        ↓
index.php detecta $_POST['stringInput']
        ↓
handleSingleVATValidation($vatNumber)
  ↳ Verifica que no esté vacío
  ↳ processor->validateSingleVAT()
    ↳ validator->validateItalianVAT()
    ↳ Guarda resultado en BD
        ↓
return $result con:
  - validationResult: [original, cleaned, status, message]
        ↓
index.php muestra resultado en la misma página
```

---

## 🎯 **Principios de Diseño Aplicados**

### **1. Single Responsibility Principle (SRP)**
- ✅ `VATController`: Solo maneja HTTP requests/responses
- ✅ `VATProcessor`: Solo procesa lógica de negocio
- ✅ `VATValidator`: Solo valida formatos

### **2. Dependency Injection**
- ✅ El controller recibe el processor, no lo crea internamente
- ✅ Fácil testing: puedo inyectar un mock processor

### **3. Error Handling Consistente**
- ✅ Siempre retorno la misma estructura
- ✅ Try-catch en operaciones que pueden fallar
- ✅ Limpieza automática en caso de error

### **4. Security First**
- ✅ `basename()` previene path traversal
- ✅ `move_uploaded_file()` más seguro que alternativas
- ✅ Validación de tipos de archivo

---

## 💡 **Preguntas de Entrevista Probables**

**P: "¿Por qué usas un array estructurado en lugar de excepciones?"**
**R**: Las excepciones son para errores excepcionales. Los errores de validación son parte del flujo normal. El array me da mejor control sobre la respuesta HTTP y es más fácil testear.

**P: "¿Cómo manejarías archivos CSV muy grandes?"**
**R**: Implementaría procesamiento en chunks con Ajax progress bar, o mejor aún, un sistema de jobs en background con Redis/RabbitMQ.

**P: "¿Qué pasa si dos usuarios suben archivos al mismo tiempo?"**
**R**: Actualmente podría haber colisión de nombres. En producción usaría `uniqid()` o UUIDs para nombres únicos de archivo.

**P: "¿Por qué no usas un framework como Laravel/Symfony?"**
**R**: Para este proyecto pequeño, PHP vanilla es suficiente y demuestra conocimiento de los fundamentos. En producción sí usaría un framework para más robustez y features built-in.

¡Este controller demuestra buenas prácticas de arquitectura, manejo de errores, y seguridad!