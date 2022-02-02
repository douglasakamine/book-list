<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Books;
use Response;
use File;

class BooksController extends Controller
{
    /**
     * Retrieves books data from Mysql DB
     *
     * @param Request $request
     * @return Collection $result
     */
    public function getBooks(Request $request) {

        $booksQuery = Books::select('id','title','author')->orderBy('created_at','DESC');
        $searchCriteria = $request->input('searchCriteria');

        if($searchCriteria !== null) {
            $booksQuery = $this->filterBySearchCriteria($booksQuery, $searchCriteria);
        }
        $result = $booksQuery->get();
        return $result;
    }

    /**
     * Save book title and author to Mysql DB
     *
     * @param Request $request
     */
    public function saveBook(Request $request) {

        $update = new Books;
        $update->title = $request->input('title');
        $update->author = $request->input('author');
        $update->save();
    }

    /**
     * Edit book title or author to Mysql DB
     *
     * @param Request $request
     */
    public function editBook(Request $request) {

        $bookId = $request->input('id');
        $update = Books::findOrFail($bookId);
        $update->title = $request->input('title');
        $update->author = $request->input('author');
        $update->save();
    }

    /**
     * Delete book data from Mysql DB
     *
     * @param string $id book id
     */
    public function deleteBook(string $id) {

        $result = Books::where('id', $id)->delete();
    }

    /**
     * Filter query by a search word criteria
     *
     * @param Query $query raw query from getBooks
     * @param string $searchCriteria word inserted in search bar
     * @return Query $filtered query
     */
    private function filterBySearchCriteria($query, $searchCriteria) {

        $filteredQuery = $query->where('title', 'LIKE', "%{$searchCriteria}%")
            ->orWhere('author', 'LIKE', "%{$searchCriteria}%");
        return $filteredQuery;
    }

    /**
     * Return data from DB in CSV format
     *
     * @param Request $request
     * @return Response csv file to download
     */
    public function exportDataToCsv(Request $request) {

        $title = $request->input('title');
        $author = $request->input('author');

        $books = Books::get();
        $headers = ['Content-Type' => 'application/vnd.ms-excel; charset=utf-8'];

        if (!File::exists(public_path()."/files")) {
            File::makeDirectory(public_path() . "/files");
        }
        $filename =  public_path("files/books.csv");
        $handle = fopen($filename, 'w');

        //Building the file header
        $arrayFileHeader = array();
        if($title) array_push($arrayFileHeader, "Title");
        if($author) array_push($arrayFileHeader, "Author");
        fputcsv($handle, $arrayFileHeader);

        //Building the file body
        foreach ($books as $row) {
            $arrayFileBody = array();
            if($title) array_push($arrayFileBody, $row->title);
            if($author) array_push($arrayFileBody, $row->author);
            fputcsv($handle, $arrayFileBody);
        }
        fclose($handle);

        return Response::download($filename, "books.csv", $headers);
    }

    /**
     * Return data from DB in XML format
     *
     * @param Request $request
     * @return object xml file
     */
    public function exportDataToXml(Request $request) {

        $title = $request->input('title');
        $author = $request->input('author');

        $books = Books::get();
        $xml = new \SimpleXMLElement("<root/>");

        foreach ($books as $row) {
            $booksElement = $xml->addChild("books");
            if($title) $booksElement->addChild("title", $row->title);
            if($author) $booksElement->addChild("author", $row->author);
        }
        header("Content-type: text/xml");
        header('Content-Disposition: attachment; filename="books.xml"');
        return $xml->asXML();
    }
}
