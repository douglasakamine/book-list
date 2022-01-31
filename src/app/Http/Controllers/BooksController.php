<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use Response;
use File;

class BooksController extends Controller
{
    public function getBooks(Request $request) {

        $booksQuery = Books::select('id','title','author')->orderBy('created_at','DESC');
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

    //
    public function exportDataToCsv() {

        $books = Books::get();
        $headers = ['Content-Type' => 'application/vnd.ms-excel; charset=utf-8'];

        if (!File::exists(public_path()."/files")) {
            File::makeDirectory(public_path() . "/files");
        }
        $filename =  public_path("files/books.csv");
        $handle = fopen($filename, 'w');

        fputcsv($handle, [
            "Title",
            "Author",
        ]);

        foreach ($books as $row) {
            fputcsv($handle, [
                $row->title,
                $row->author,
            ]);
        }
        fclose($handle);

        return Response::download($filename, "books.csv", $headers);
    }

    public function exportDataToXml() {

        $books = Books::get();
        $xml = new \SimpleXMLElement("<root/>");

        foreach ($books as $row) {
            $booksElement = $xml->addChild("books");
            $booksElement->addChild("title", $row->title);
            $booksElement->addChild("author", $row->author);
        }
        header("Content-type: text/xml");
        header('Content-Disposition: attachment; filename="books.xml"');
        return $xml->asXML();
    }

}
