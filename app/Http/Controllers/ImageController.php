<?php

namespace App\Http\Controllers;

use App\Http\Resources\Image as ResourcesImage;
use App\Models\Event;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    private string $storageBasePath = 'images/events/';

    /**
     * Display a listing of the images.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ResourcesImage::collection(Image::orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created image in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fileDestinationPath = '';
        $file = '';
        $validator = Validator::make($request->all(), Image::createRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if ($file = $request->file('file')) {
                $fileDestinationPath = $this->storageBasePath.Event::find($request->input('event_id'))->year.'/'.$this->normalizeEventName(Event::find($request->input('event_id'))->name).'/';
                if (! File::exists($fileDestinationPath.$this->normalizeEventName(Event::find($request->input('event_id'))->name).'__'.$file->getClientOriginalName())) {
                    $file->move('../'.$fileDestinationPath, $this->normalizeEventName(Event::find($request->input('event_id'))->name).'__'.$file->getClientOriginalName());
                    if (
                        Image::create([
                            'event_id' => $request->input('event_id'),
                            'path' => $fileDestinationPath,
                            'name' => $this->normalizeEventName(Event::find($request->input('event_id'))->name).'__'.pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                            'extension' => pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
                            'alt' => $request->input('alt'),
                            'title' => $request->input('title'),
                        ])
                    ) {
                        return response()->json([
                            'message' => 'Image cr????e avec succ??s',
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'message' => 'Erreur lors de la cr??ation de l\'image',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'message' => 'L\'image existe d??j??',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            } else {
                return response()->json([
                    'message' => 'Pas d\'image dans la requ??te',
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Display the specified image.
     *
     * @param  \App\Models\Image  $Image
     * @return \Illuminate\Http\Response
     */
    public function show(Image $image)
    {
        return new ResourcesImage($image);
    }

    /**
     * Update the specified image in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $image_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Image $image)
    {
        $fileDestinationPath = '';
        $file = '';
        $validator = Validator::make($request->all(), Image::updateRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if ($file = $request->file('file')) {
                $path = storage_path().'../'.$image->path.$image->name.'.'.$image->extension;
                if (File::exists($path)) {
                    if (unlink($path)) {
                        if (! File::exists($path)) {
                            if (intval($request->input('event_id')) !== $image->event_id) {
                                $image->update([
                                    'event_id' => intval($request->input('event_id')),
                                ]);
                            }
                            $fileDestinationPath = $this->storageBasePath.Event::find($image->event_id)->year.'/'.$this->normalizeEventName(Event::find($image->event_id)->name).'/';
                            if (
                                $image->update([
                                    'path' => $fileDestinationPath,
                                    'name' => $this->normalizeEventName(Event::find($image->event_id)->name).'__'.pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                                    'extension' => pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
                                    'alt' => $request->input('alt'),
                                    'title' => $request->input('title'),
                                ])
                            ) {
                                $file->move('storage/'.$fileDestinationPath, $image->name.'.'.$image->extension);

                                return response()->json([
                                    'message' => 'Image modifi??e avec succ??s',
                                ], Response::HTTP_OK);
                            } else {
                                return response()->json([
                                    'message' => 'Erreur lors de la modification de l\'image',
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        } else {
                            return response()->json([
                                'message' => 'Erreur lors de la supression de l\'image',
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else {
                        return response()->json([
                            'message' => 'Erreur lors de la supression de l\'ancienne image',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'message' => 'Le fichier n\'a pas ??t?? trouv??',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'error' => 'Pas d\'image dans la requ??te',
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Remove the specified image from storage.
     *
     * @param  \App\Models\Image  $image
     */
    public function destroy(Image $image)
    {
        $imageStoragePath = storage_path().'../'.$image->path;
        $path = $imageStoragePath.$image->name.'.'.$image->extension;
        if (File::exists($path)) {
            if (unlink($path) && $image->delete()) {
                return response()->json([
                    'message' => 'Image supprim??e avec succ??s',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Erreur lors de la suppression de l\'image',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json([
                'message' => 'Le fichier n\'a pas ??t?? trouv??',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Method file [Get file of an image]
     *
     * @param  int  $image_id [Image id]
     * @return Response
     */
    public function file(Image $image)
    {
        //php artisan storage:link before using this method
        if ($file = public_path().'../../'.$image->path.$image->name.'.'.$image->extension) {
            $response = FacadesResponse::make(file_get_contents($file), 200);
            $response->header('Content-Type', 'image/'.$image->extension);

            return $response;
        } else {
            return response()->json([
                'message' => 'Le fichier n\'a pas ??t?? trouv??',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Method file [Get file of an image]
     *
     * @param  int  $image_id [Image id]
     * @return Response
     */
    public function download(Image $image)
    {
        //php artisan storage:link before using this method
        if ($file = public_path().'../../'.$image->path.$image->name.'.'.$image->extension) {
            return response()->download($file);
        } else {
            return response()->json([
                'message' => 'Le fichier n\'a pas ??t?? trouv??',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Method normalizeEventName [Normalize event name]
     *
     * @param  string  $eventName [Event name]
     * @return string [Normalized event name]
     */
    private function normalizeEventName(string $eventName): string
    {
        $allAccentLetters = ['??' => 'S', '??' => 's', '??' => 'Z', '??' => 'z', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'C', '??' => 'E', '??' => 'E',
            '??' => 'E', '??' => 'E', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'N', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'U',
            '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'Y', '??' => 'B', '??' => 'Ss', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'c',
            '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'o', '??' => 'n', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o',
            '??' => 'o', '??' => 'o', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'y', '??' => 'b', '??' => 'y', ];
        $eventName = strtr($eventName, $allAccentLetters);
        $allSpecialChars = ['/', '\\', ':', ';', '!', '@', '#', '$', '%', '^', '*', '(', ')', '+', '=', '|', '{', '}', '[', ']', '"', "'", '<', '>', ',', '?', '~', '`', '&', ' ', '.'];
        $replace = array_combine($allSpecialChars, array_fill(0, count($allSpecialChars), '_'));
        $eventName = strtr($eventName, $replace);
        $eventName = preg_replace('/_+/', '_', $eventName);
        $eventName = strtolower($eventName);

        return $eventName;
    }
}
