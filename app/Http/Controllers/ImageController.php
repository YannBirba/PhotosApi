<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Http\Resources\Image as ResourcesImage;
use App\Models\Event;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
        return Image::all();
    }

    /**
     * Store a newly created image in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fileDestinationPath ='';
        $file = '';
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg|max:8192',
        ]);
        if ($file = $request->file('file')) {
            $fileDestinationPath = $this->storageBasePath . Event::find($request->input('event_id'))->year . '/' . $this->normalizeEventName(Event::find($request->input('event_id'))->name) . '/';
            if (!File::exists($fileDestinationPath . $this->normalizeEventName(Event::find($request->input('event_id'))->name) . '__' . $file->getClientOriginalName())) {
                $file->move('storage/'.$fileDestinationPath, $this->normalizeEventName(Event::find($request->input('event_id'))->name) . '__' . $file->getClientOriginalName());
                if(
                    Image::create([
                        'event_id' => $request->input('event_id'),
                        'path' => $fileDestinationPath,
                        'name' => $this->normalizeEventName(Event::find($request->input('event_id'))->name) . '__' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'extension' => pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
                        'alt' => $request->input('alt'),
                        'title' => $request->input('title'),
                ])
                ){
                    return response()->json([
                        'success' => 'Image créée avec succès',
                    ],200
                    );
                }
                else
                {
                    return response()->json([
                        'error' => 'Erreur lors de la création de l\'image'
                        ] ,500
                    );
                }
            }
            else {
                return response()->json([
                    'error' => 'L\'image existe déjà'
                ],500
                );
            }
            
        }
        else{
            return response()->json([
                'error' => 'Pas d\'image dans la requête'
                ] ,500
            );
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
     * @param  \App\Models\Topicality  $topicality
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Image $image)
    {
        if($image->update($request->all())){
            return response()->json([
                'success' => 'Image modifiée avec succès'
            ],200
            );
        }
        else
        {
            return response()->json([
                'error' => 'Erreur lors de la modification de l\'image'
                ] ,500
            );
        }
    }
    /**
     * Remove the specified image from storage.
     *
     * @param  \App\Models\Image $image
     * @return \Illuminate\Http\Response
     */
    public function destroy(Image $image)
    {
        if ($path = storage_path() . "\\app\\public\\images\\events\\2022\\event-test\\event-test__voltairefgh.png") {
            if (Storage::delete($path)){
                return response()->json([
                    'success' => 'Image supprimée avec succès'
                    ] ,500
                );
            }
            else {
                return response()->json([
                    'error' => 'Erreur lors de la suppression de l\'image'
                    ] ,500
                );
            }
        }
        else{
            return response()->json([
                'error' => 'Le fichier n\'a pas été trouvé'
                ] ,500
            );
        }

        // if ($path = storage_path() . "\app\public\\". $image->path . $image->name . '.' . $image->extension) {
        //     Storage::delete($path);
        //     return response()->json([
        //         'success' => 'Image supprimée avec succès'
        //         ] ,500
        //     );
        // }
        // else{
        //     return response()->json([
        //         'error' => 'Le fichier n\'a pas été trouvé'
        //         ] ,500
        //     );
        // }
        // if($image->delete()){
        //     return response()->json([
        //         'success' => 'Image supprimée avec succès'
        //     ],200
        //     );
        // }
        // else
        // {
        //     return response()->json([
        //         'error' => 'Erreur lors de la suppression de l\'image'
        //         ] ,500
        //     );
        // }
    }
       /**
     * Remove the specified image from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        $data = $_GET['title'];
        if($images = Image::where('title', 'like', "%{$data}%")->get()){
            return response()->json([
                'data' => $images
            ],200
        ); 
        }
        else{
            return response()->json([
                'error' => 'Erreur lors de la recherche'
            ],500
        ); 
        }
    }

    /**
     * Method get event of an image
     *
     * @param int $image_id [Image id]
     *
     * @return Event
     */
    public function event(int $image_id)
    {
        if($event = Image::find($image_id)->event){
            return $event;
        }
        else {
            return response()->json([
                'error' => 'L\'événement de l\'image n\'a pas été trouvé'
                ] ,500
            );
        }
    }

        
    /**
     * Method file [Get file of an image]
     *
     * @param int $image_id [Image id]
     *
     * @return Response
     */
    public function file(int $image_id)
    { 
        if($image = Image::find($image_id)){          
            //php artisan storage:link before using this method
            if ($path = storage_path() . "\app\public\\". $image->path . $image->name . '.' . $image->extension) {
                return response()->download($path);
            }
            else{
                return response()->json([
                    'error' => 'Le fichier n\'a pas été trouvé'
                    ] ,500
                );
            }
        }
        else {
            return response()->json([
                'error' => 'L\'image n\'a pas été trouvée'
                ] ,500
            );
        }
    }
    
    /**
     * Method normalizeEventName [Normalize event name]
     *
     * @param string $eventName [Event name]
     *
     * @return string [Normalized event name]
     */
    private function normalizeEventName(string $eventName) :string
    {
        $allAccentLetters = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $eventName = strtr( $eventName, $allAccentLetters );
        $allSpecialChars = array('/','\\',':',';','!','@','#','$','%','^','*','(',')','+','=','|','{','}','[',']','"',"'",'<','>',',','?','~','`','&',' ','.');
        $replace = array_combine($allSpecialChars, array_fill(0, count($allSpecialChars), '_'));
        $eventName = strtr($eventName, $replace);
        $eventName = preg_replace('/_+/', '_', $eventName);
        $eventName = strtolower($eventName);
        return $eventName;
    }
}
