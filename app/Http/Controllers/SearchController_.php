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
			$detail = array();
			if($found_items > 24){
				for($offset = 0 ; $offset <= 4 ; $offset++){
					$get = $client->get('https://redsky.target.com/v1/plp/search/?count=24&offset='.$offset.'&keyword='.$term);
					$response = json_decode($get->getBody());
					$found_title = count($response->search_response->items->Item);
					for($i = 0 ; $i < $found_title ; $i++){
						$list = $response->search_response->items->Item[$i];
						$title = (isset($list->title)) ? $list->title : null ;
						$tcin = (isset($list->tcin)) ? $list->tcin : null;
						$url = (isset($list->url)) ? "https://www.target.com".$list->url : null;
						$description = (isset($list->description)) ? $list->description : null;
						$merch_sub_class = (isset($list->merch_sub_class)) ? $list->merch_sub_class : null;
						$merch_class = (isset($list->merch_class)) ? $list->merch_class : null;
						$merch_class_id = (isset($list->merch_class_id)) ? $list->merch_class_id : null;
						$brand = (isset($list->brand)) ? $list->brand : null;
						$image = (isset($list->images[0]->base_url)) ? $list->images[0]->base_url.$list->images[0]->primary : null;
						$availability_status = (isset($list->availability_status)) ? $list->availability_status : null;
						$pick_up_in_store = (isset($list->pick_up_in_store)) ? $list->pick_up_in_store : null;
						$ship_to_store = (isset($list->ship_to_store)) ? $list->ship_to_store : null;
						$rush_delivery = (isset($list->rush_delivery)) ? $list->rush_delivery : null;
						$is_out_of_stock_in_all_store_locations = (isset($list->is_out_of_stock_in_all_store_locations)) ? $list->is_out_of_stock_in_all_store_locations : null;
						$is_out_of_stock_in_all_online_locations = (isset($list->is_out_of_stock_in_all_online_locations)) ? $list->is_out_of_stock_in_all_online_locations : null;
						$list_price = (isset($list->list_price->formatted_price)) ? $list->list_price->formatted_price : null;
						
						$bullet_description = array();
						$bullet_description_count = (isset($list->bullet_description)) ? count($list->bullet_description) : 0;
						for($l = 0 ; $l < $bullet_description_count ; $l++){
							array_push($bullet_description, $list->bullet_description[$l]);
						}
						$goods = array("title" => $title);
						$goods_detil = array("product_id" => $tcin, 
												"url"=>$url,
												"description" => $description,
												"merch_sub_class" => $merch_sub_class,
												"merch_class" => $merch_class,
												"merch_class_id" => $merch_class_id,
												"brand" => $brand,
												"image" => $image,
												"availability_status" => $availability_status,
												"pick_up_in_store" => $pick_up_in_store,
												"ship_to_store" => $ship_to_store,
												"rush_delivery" => $rush_delivery,
												"is_out_of_stock_in_all_store_locations" => $is_out_of_stock_in_all_store_locations,
												"is_out_of_stock_in_all_online_locations" => $is_out_of_stock_in_all_online_locations,
												"list_price" => $list_price,
												"bullet_description" => $bullet_description
											);
						array_push($product_id,$tcin);
						array_push($goods,$goods_detil);
						array_push($json,$goods);
					
					}
					
				}
			}
			
			
			for($no = 0 ; $no < count($product_id) ; $no++){
				
				$get = $client->get('https://redsky.target.com/v2/pdp/tcin/'.$product_id[$no]);
				$response = json_decode($get->getBody());
				//$merge = array_merge($json[$no],$response);
				$merge = array_push($json[$no],$response);
			}
			
			
			header('Content-Type: application/json');
			echo json_encode($json);

		}
		catch (RequestException $e){
			$response = $this->StatusCodeHandling($e);
			return $response;
		}
	}
	
	public function StatusCodeHandling($e){
		if ($e->getResponse()->getStatusCode() == '400' || $e->getResponse()->getStatusCode() == '401' || $e->getResponse()->getStatusCode() == '403' || $e->getResponse()->getStatusCode() == '404' || $e->getResponse()->getStatusCode() == '422' || $e->getResponse()->getStatusCode() == '500'){
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
		else{
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
	}
}
