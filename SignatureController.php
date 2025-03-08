<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    /**
     * Show the signature form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('signatures.create');
    }

    /**
     * Store the signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // Validate the base64 image data
        $imageData = $request->input('signature_image');
        
        // Check if it's a valid base64 image
        if (!$this->isValidBase64Image($imageData)) {
            return redirect()->back()
                ->withErrors(['signature_image' => 'The signature provided is not a valid image.'])
                ->withInput();
        }

        // Process and store the signature
        $filename = $this->storeSignature($imageData);

        // Here you would typically associate the signature with a user/document
        // and store the association in your database
        
        // Example:
        // $document = Document::find($request->document_id);
        // $document->signature_path = $filename;
        // $document->save();

        // Redirect with success message
        return redirect()->route('signatures.success')
            ->with('success', 'Signature has been successfully saved.');
    }

    /**
     * Check if the string is a valid base64 encoded image.
     *
     * @param  string  $data
     * @return bool
     */
    protected function isValidBase64Image($data)
    {
        // Check if it's a valid data URL format
        if (!preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            return false;
        }

        // Get the image data without the prefix
        $data = substr($data, strpos($data, ',') + 1);
        $data = str_replace(' ', '+', $data);
        
        // Decode and check if it's valid
        $decodedData = base64_decode($data);
        if (!$decodedData) {
            return false;
        }

        // Check minimum size for a valid image (to prevent empty signatures)
        if (strlen($decodedData) < 100) {
            return false;
        }

        return true;
    }

    /**
     * Store the signature image.
     *
     * @param  string  $imageData
     * @return string  The stored filename
     */
    protected function storeSignature($imageData)
    {
        // Extract the image data
        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $imageData = str_replace(' ', '+', $imageData);
        
        // Decode base64 data
        $decodedData = base64_decode($imageData);
        
        // Generate a unique filename
        $filename = 'signatures/'. Str::uuid() . '.png';
        
        // Store the file
        Storage::disk('public')->put($filename, $decodedData);
        
        return $filename;
    }

    /**
     * Display success page.
     *
     * @return \Illuminate\View\View
     */
    public function success()
    {
        return view('signatures.success');
    }
}
