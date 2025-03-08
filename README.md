# Laravel Touch Signature Pad

A responsive touch signature solution for Laravel applications that captures signatures via HTML5 Canvas, converts them to images, and handles validation and storage.

## Features

- ✍️ Responsive signature pad for desktop and touch devices
- 📱 Mobile-friendly with proper touch event handling
- 👁️ Real-time signature preview
- 🔒 Client and server-side validation
- 💾 Secure storage as PNG files with unique IDs

## Quick Implementation

### 1. Add routes to your `routes/web.php`

```php
use App\Http\Controllers\SignatureController;

Route::get('/signature', [SignatureController::class, 'create'])->name('signatures.create');
Route::post('/signature/store', [SignatureController::class, 'store'])->name('signatures.store');
Route::get('/signature/success', [SignatureController::class, 'success'])->name('signatures.success');
```

### 2. Create the Controller

Copy the `SignatureController.php` file to your `app/Http/Controllers/` directory or create it with this content:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    public function create()
    {
        return view('signatures.create');
    }

    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'signature_image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate and store the signature
        if (!$this->isValidBase64Image($request->input('signature_image'))) {
            return redirect()->back()
                ->withErrors(['signature_image' => 'Invalid signature image.'])
                ->withInput();
        }

        $filename = $this->storeSignature($request->input('signature_image'));

        // Here you would typically save the filename to your database
        // Example: $document->signature_path = $filename;
        
        return redirect()->route('signatures.success')
            ->with('success', 'Signature saved successfully.');
    }

    protected function isValidBase64Image($data)
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $data)) {
            return false;
        }
        
        $data = substr($data, strpos($data, ',') + 1);
        $data = str_replace(' ', '+', $data);
        $decodedData = base64_decode($data);
        
        return $decodedData && strlen($decodedData) > 100;
    }

    protected function storeSignature($imageData)
    {
        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $imageData = str_replace(' ', '+', $imageData);
        $decodedData = base64_decode($imageData);
        
        $filename = 'signatures/'. Str::uuid() . '.png';
        Storage::disk('public')->put($filename, $decodedData);
        
        return $filename;
    }

    public function success()
    {
        return view('signatures.success');
    }
}
```

### 3. Create the View

Create a file at `resources/views/signatures/create.blade.php` with the HTML/JS for the signature pad. You can copy it directly from the repository or use the standalone HTML file as a reference.

Make sure your application has:
- A public storage link (`php artisan storage:link`)
- A `signatures` directory in your storage path

## Integration with Existing Forms

To add the signature pad to your existing form:

1. Include the canvas element and related JavaScript 
2. Add a hidden input field to capture the signature data:
   ```html
   <input type="hidden" name="signature_image" id="signature-data">
   ```
3. Call the signature capture function before form submission

## License

This project is licensed under the MIT License.