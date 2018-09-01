<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(){
		$data['title'] = "Scrapper!";
		return view('search',$data);
	}
	public function result(Request $request){
		$this->validate($request, [
				'search' => 'required|max:255'
		]);
		
		$term = str_replace(" ","+",$request->input('search'));
		$term = str_replace("'","",$request->input('search'));
		
		try{
			$client = new \GuzzleHttp\Client();
			$get = $client->get('https://redsky.target.com/v1/plp/search/?count=1&offset=0&keyword='.$term);
			$response = json_decode($get->getBody());
			$found_items = (int)$response->search_response->metaData[4]->value;
			$json = array("search_term" => $request->input('search'), "items_found" => $found_items);
			
			$product_id = array();
			$product = array();
			$k = ($found_items > 24) ? 4 : 0 ;
			for($offset = 0 ; $offset <= $k ; $offset++){
				$get = $client->get('https://redsky.target.com/v1/plp/search/?count=24&offset='.$offset.'&keyword='.$term);
				$response = json_decode($get->getBody());
				$list = $response->search_response->items->Item;
				$product = array_merge($product, $list);
			}
			array_push($json,$product);
			$count = (int)count($product);
			for($no = 0 ; $no < $count ; $no++){
				$product_id = $product[$no]->tcin;
				$get = $client->get('https://redsky.target.com/v2/pdp/tcin/'.$product_id);
				$response = json_decode($get->getBody());
				$json[0][$no]->detail = $response;
			}
			header('Content-Type: application/json');
			echo json_encode($json);
		}
		catch (\Exception $e){
			$response = $e->getMessage();
			return "Error Happened : ".$response;
		}
	}
}
