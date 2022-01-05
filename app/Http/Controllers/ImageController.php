<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Http\Resources\Image as ResourcesImage;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ImageController extends Controller
{
    private string $storageBasePath = '/public/images/events/';

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
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:8192',
        ]);
        $input = $request->all();
        if ($file = $request->file('file')) {
            $fileDestinationPath = $this->storageBasePath . date('Y') . '/' . Event::find($request->input('event_id'))->name;
            $imageFile = $file->getClientOriginalName() . "." . $file->getClientOriginalExtension();
            $file->move($fileDestinationPath, $imageFile);
            $input['file'] = "$imageFile";
        }

        if(
            Image::create([
                'event_id' => $request->input('event_id'),
                'path' => $fileDestinationPath,
                'name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'alt' => $request->input('alt'),
                'title' => $request->input('title'),
                'file' => $input['file']
        ])
        ){
            return response()->json([
                'success' => 'Image créée avec succès'
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
     * @param  \App\Models\Topicality  $topicality
     * @return \Illuminate\Http\Response
     */
    public function destroy(Image $image)
    {

        if($image->delete()){
            return response()->json([
                'success' => 'Image supprimée avec succès'
            ],200
            );
        }
        else
        {
            return response()->json([
                'error' => 'Erreur lors de la suppression de l\'image'
                ] ,500
            );
        }
    }
       /**
     * Remove the specified image from storage.
     *
     * @param  \App\Models\Topicality  $topicality
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
}
