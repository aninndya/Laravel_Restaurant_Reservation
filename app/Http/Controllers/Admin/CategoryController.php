<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Retrieve the uploaded image file
        $image = $request->file('image');
    
        // Generate a unique filename for the image
        $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
    
        // Move the image file to the desired directory
        $image->move(public_path('categories'), $imageName);
    
        // Update the image column with the new filename
        $validatedData['image'] = 'categories/' . $imageName;
    
        // Create the category with the validated data and updated image filename
        Category::create($validatedData);
    
        // Redirect or perform other actions after successful creation
        return to_route('admin.categories.index')->with('success', 'Category berhasil dibuat.');
    }

    /**
     * Display specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit($id)
{
    try {
        // Retrieve the category by ID
        $category = Category::findOrFail($id);
        
        // Pass the category to the view for editing
        return view('admin.categories.edit', compact('category'));
    } catch (ModelNotFoundException $exception) {
        // Category not found, handle the error (e.g., show an error page, redirect, etc.)
        // ...
    }
}

public function update(Request $request, $id)
{
    try {
        // Retrieve the category by ID
        $category = Category::findOrFail($id);
    } catch (ModelNotFoundException $exception) {
        // Category not found, handle the error (e.g., show an error page, redirect, etc.)
        // ...
    }

    // Validate the form data
    $validatedData = $request->validate([
        'name' => 'required',
        'description' => 'required',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Update the category data
    $category->name = $validatedData['name'];
    $category->description = $validatedData['description'];

    if ($request->hasFile('image')) {
        // Retrieve the uploaded image file
        $image = $request->file('image');

        // Generate a unique filename for the image
        $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();

        // Move the image file to the desired directory
        $image->move(public_path('categories'), $imageName);

        // Delete the old image file, if it exists
        if ($category->image) {
            $oldImagePath = public_path($category->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Update the image column with the new filename
        $category->image = 'categories/' . $imageName;
    }

    // Save the updated category
    $category->save();
    return to_route('admin.categories.index')->with('success', 'Category berhasil diupdate.');
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        Storage::delete($category->image);
        $category->menus()->detach();
        $category->delete();

        return to_route('admin.categories.index')->with('danger', 'Category berhasil dihapus.');
    }
}
