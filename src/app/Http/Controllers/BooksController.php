<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Books;

class BooksController extends Controller
{
    public function saveBook() {

        $update = new Books;
        $update->title = "test";
        $update->author = "test2";
        $update->save();

    }

    public function getBooks() {

        $queryGetBooks = Books::select('id','title','author');
        $result = $queryGetBooks->get();
        return $result;
    }

}
