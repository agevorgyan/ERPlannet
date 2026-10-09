<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Upload an image or media asset to public storage.
     */
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required_without:image', 'file', 'image', 'max:10240', 'mimes:jpeg,png,jpg,webp,svg,gif'],
            'image' => ['required_without:file', 'file', 'image', 'max:10240', 'mimes:jpeg,png,jpg,webp,svg,gif'],
            'folder' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $request->file('file') ?? $request->file('image');

        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'Ֆայլը չի գտնվել:',
            ], 422);
        }

        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $request->input('folder', 'general'));
        if (empty($folder)) {
            $folder = 'general';
        }

        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = Str::random(24).'.'.$extension;

        $path = $file->storeAs("uploads/{$folder}", $filename, 'public');

        $url = '/storage/'.$path;

        return response()->json([
            'success' => true,
            'message' => 'Պատկերը հաջողությամբ վերբեռնվեց:',
            'data' => [
                'url' => $url,
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ],
        ], 201);
    }
}
