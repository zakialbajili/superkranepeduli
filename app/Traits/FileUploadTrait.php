<?php

namespace App\Traits;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

trait FileUploadTrait
{
    public function uploadImage(Request $request, $inputName, $path, $required = "", $disk = "Public")
    {
        if ($request->hasFile($inputName)) {
            $image = $request->{$inputName};
            $ext = $image->getClientOriginalExtension();
            $result = Storage::disk('s3')->put($path, $image);

            return $result;
            if ($required != "") {
                $requiredRaw = explode("|", $required);

                if (array_search($ext, $requiredRaw, true) >= 0) {

                    $imageName = 'media_' . uniqid() . '.' . $ext;

                    $image->move(public_path($path), $imageName);

                    return $path . '/' . $imageName;
                }
            } else {
                $imageName = 'media_' . uniqid() . '.' . $ext;


                $image->move(public_path($path), $imageName);

                return $path . '/' . $imageName;
            }
        }
    }

    public function uploadMultiImage(Request $request, $inputName, $path, $disk = "Public")
    {
        $imagePaths = [];

        if ($request->hasFile($inputName)) {

            $images = $request->{$inputName};

            foreach ($images as $image) {
                $ext = $image->getClientOriginalExtension();
                $filename = $image->getClientOriginalName();
                $ext = strtolower($ext);
                if (strtolower($disk) == "public") {

                    $imageName = 'media_' . uniqid() . '.' . $ext;

                    $image->move(public_path($path), $imageName);

                    $imagePaths[] = [
                        "uid" => Uuid::uuid4(),
                        "filename" => $filename,
                        "ext" => $ext,
                        "path" => "/" . $path . '/' . $imageName
                    ];
                } else {
                    $imagePaths[] = [
                        "uid" => Uuid::uuid4(),
                        "filename" => $filename,
                        "ext" => $ext,
                        "path" => "/" . Storage::disk('s3')->put($path, $image)
                    ];
                }
            }

            return $imagePaths;
        }
    }

    public function updateImage(Request $request, $inputName, $path, $oldPath = null)
    {
        if ($request->hasFile($inputName)) {
            if (File::exists(public_path($oldPath))) {
                File::delete(public_path($oldPath));
            }

            $image = $request->{$inputName};
            $ext = $image->getClientOriginalExtension();
            $imageName = 'media_' . uniqid() . '.' . $ext;

            $image->move(public_path($path), $imageName);

            return $path . '/' . $imageName;
        }
    }

    /** Handle Delte File */
    public function deleteImage(string $path)
    {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

}

