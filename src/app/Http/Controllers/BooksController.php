<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use Illuminate\Support\Facades\Log;

class BooksController extends Controller
{
    public function getBooks(Request $request) {

        $booksQuery = Books::select('id','title','author');
        $searchCriteria = $request->input('searchCriteria');

        if($searchCriteria !== null) {
            $booksQuery = $this->filterBySearchCriteria($booksQuery, $searchCriteria);
        }
        $result = $booksQuery->get();
        return $result;
    }

    public function saveBook(Request $request) {

        $update = new Books;
        $update->title = $request->input('title');
        $update->author = $request->input('author');
        $update->save();

        return $request;
    }

    public function deleteBook(string $id) {

        $result = Books::where('id', $id)->delete();
        return $result;
    }

    private function filterBySearchCriteria($query, $searchCriteria) {

        $filteredQuery = $query->where('title', 'LIKE', "%{$searchCriteria}%")
            ->orWhere('author', 'LIKE', "%{$searchCriteria}%");
        return $filteredQuery;
    }



}
