<?php

namespace App\Http\Controllers;

use App\Http\Resources\Image as ResourcesImage;
use App\Models\Event;
use App\Models\Image;
use App\Utils\CacheHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    private string $storageBasePath = 'images/events/';

    /**
     * Display a listing of the images.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ResourcesImage::collection(Image::orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created image in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $fileDestinationPath = '';
        $file = '';
        $validator = Validator::make($request->all(), Image::createRules());

        $event = Event::find($request->input('event_id'));
        if ($event instanceof Collection) {
            $event = $event->first();
        }

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $event) {
            return response()->json(['message' => "L'événement n'existe pas"], Response::HTTP_NOT_FOUND);
        }

        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            return response()->json(['message' => "Plusieurs fichiers ont été envoyés"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($file instanceof UploadedFile && $file->isValid() && is_string($event->name)) {
            $fileDestinationPath = $this->storageBasePath.$event->year.'/'.$this->normalizeEventName($event->name).'/';
            if (! File::exists($fileDestinationPath.$this->normalizeEventName($event->name).'__'.$file->getClientOriginalName())) {
                $file->move('../'.$fileDestinationPath, $this->normalizeEventName($event->name).'__'.$file->getClientOriginalName());
                if (
                    $image = Image::create([
                        'event_id' => $request->event_id,
                        'path' => $fileDestinationPath,
                        'name' => $this->normalizeEventName($event->name).'__'.pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'extension' => pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
                        'alt' => $request->input('alt'),
                        'title' => $request->input('title'),
                    ])
                ) {
                    return response()->json([
                        'message' => "Image créée avec succès",
                        'data' => CacheHelper::get($image),
                    ], Response::HTTP_CREATED);
                }

                return response()->json([
                    'message' => "Erreur lors de la création de l'image",
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => "L'image existe déjà",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => "Il n'y a pas d'image dans la requête ou elle est invalide",
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified image.
     *
     * @param  Image  $image
     * @return ResourcesImage
     */
    public function show(Image $image): ResourcesImage
    {
        return new ResourcesImage($image);
    }

    /**
     * Update the specified image in storage.
     *
     * @param  Request  $request
     * @param  Image  $image
     * @return JsonResponse
     */
    public function update(Request $request, Image $image): JsonResponse
    {
        $fileDestinationPath = '';
        $file = '';
        $validator = Validator::make($request->all(), Image::updateRules());

        $event = Event::find($request->input('event_id'));
        if ($event instanceof Collection) {
            $event = $event->first();
        }

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $event) {
            return response()->json(['message' => "L'événement n'existe pas"], Response::HTTP_NOT_FOUND);
        }

        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            return response()->json(['message' => "Plusieurs fichiers ont été envoyés"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($file instanceof UploadedFile && $file->isValid() && is_string($event->name)) {
            $path = storage_path().'../'.$image->path.$image->name.'.'.$image->extension;
            if (File::exists($path)) {
                if (unlink($path)) {
                    if (! File::exists($path)) {
                        if (intval($request->input('event_id')) !== $image->event_id) {
                            $image->update([
                                'event_id' => intval($request->input('event_id')),
                            ]);
                        }
                        $fileDestinationPath = $this->storageBasePath.$event->year.'/'.$this->normalizeEventName($event->name).'/';
                        if (
                            $image->update([
                                'path' => $fileDestinationPath,
                                'name' => $this->normalizeEventName($event->name).'__'.pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                                'extension' => pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
                                'alt' => $request->input('alt'),
                                'title' => $request->input('title'),
                            ])
                        ) {
                            $file->move('storage/'.$fileDestinationPath, $image->name.'.'.$image->extension);

                            return response()->json([
                                'message' => 'Image modifiée avec succès',
                            ], Response::HTTP_OK);
                        }

                        return response()->json([
                            'message' => "Erreur lors de la modification de l'image",
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    return response()->json([
                        'message' => "Erreur lors de la supression de l'image",
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                return response()->json([
                    'message' => "Erreur lors de la supression de l'ancienne image",
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => "Le fichier n'a pas été trouvé",
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'error' => "Pas d'image dans la requête",
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified image from storage.
     *
     * @param  Image  $image
     * @return JsonResponse
     */
    public function destroy(Image $image): JsonResponse
    {
        $imageStoragePath = storage_path().'../'.$image->path;
        $path = $imageStoragePath.$image->name.'.'.$image->extension;
        if (File::exists($path)) {
            if (unlink($path) && $image->delete()) {
                return response()->json([
                    'message' => 'Image supprimée avec succès',
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'image',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Le fichier n\'a pas été trouvé',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method file [Get file of an image]
     *
     * @param  Image  $image [The image to get file]
     * @return JsonResponse
     */
    public function file(Image $image): JsonResponse
    {
        //php artisan storage:link before using this method
        if ($file = public_path().'../../'.$image->path.$image->name.'.'.$image->extension) {
            // $response = FacadesResponse::json(file_get_contents($file), 200);
            // $response->header('Content-Type', 'image/' . $image->extension);
            return response()->json([
                'file' => file_get_contents($file),
            ], Response::HTTP_OK, [
                'Content-Type' => 'image/'.$image->extension,
            ]);
        }

        return response()->json([
            'message' => 'Le fichier n\'a pas été trouvé',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method file [Get file of an image]
     *
     * @param  Image  $image [The image to download file]
     * @return BinaryFileResponse|JsonResponse
     */
    public function download(Image $image): BinaryFileResponse | JsonResponse
    {
        //php artisan storage:link before using this method
        if ($file = public_path().'../../'.$image->path.$image->name.'.'.$image->extension) {
            return response()->download($file);
        }

        return response()->json([
            'message' => 'Le fichier n\'a pas été trouvé',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method normalizeEventName [Normalize event name]
     *
     * @param  string  $eventName [Event name]
     * @return string [Normalized event name]
     */
    private function normalizeEventName(string $eventName): string
    {
        $allAccentLetters = [
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y',
        ];
        $eventName = strtr($eventName, $allAccentLetters);
        $allSpecialChars = ['/', '\\', ':', ';', '!', '@', '#', '$', '%', '^', '*', '(', ')', '+', '=', '|', '{', '}', '[', ']', '"', "'", '<', '>', ',', '?', '~', '`', '&', ' ', '.'];
        $replace = array_combine($allSpecialChars, array_fill(0, count($allSpecialChars), '_'));
        $eventName = strtr($eventName, $replace);
        $eventName = (string) preg_replace('/_+/', '_', $eventName);
        $eventName = strtolower($eventName);

        return $eventName;
    }
}
