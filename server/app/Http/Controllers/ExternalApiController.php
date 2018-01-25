<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ExternalApiController extends Controller
{

    public function __construct(Client $client)
    {
        $this->client       = $client;
        $this->api_key      = env('USER_API_KEY');
        $this->user_secrete = env('USER_SECRET_KEY');
        $this->api_url      = env('EXTERNAL_API_URL');
    }

    public function trips()
    {

        $res = $this->client->request('GET', $this->api_url . '/admin/trips.json?ac_api_key=' . $this->api_key . '&user_secret=' . $this->user_secrete . '&offset=0&count=true&totalCount=true&limit=1000&order_by[]=state.asc,code.asc,id.desc&search_fields[]=name,code,supplier_name&search=tri');

        $data = json_decode($res->getBody(), true);

        foreach ($data['trips'] as $dvalue) {
            $final_data[] = [
                "id"   => $dvalue['id'],
                "name" => $dvalue['name'],
                "code" => $dvalue['code'],
            ];
        }

        return [
            "status" => "success",
            "msg"    => "ok",
            "data"   => $final_data,
        ];
    }

    public function departures(Request $request)
    {

        $filters = $request->input('filters');

        $current_date = date('Y-m-d');
        $fromdate     = date('Y-m-d', strtotime('-60 days', strtotime($current_date))); // last 60 days
        $trip_id      = $filters['trip_id'];

        $res = $this->client->request('GET', $this->api_url . '/api/v1/admin/departures.json?ac_api_key=' . $this->api_key . '&user_secret=' . $this->user_secrete . '&departure_from=' . $fromdate . '&limit=1000&trip_ids=' . $trip_id);

        $data = json_decode($res->getBody(), true);

        $final_data = array();
        foreach ($data['departures'] as $dvalue) {

            $end_date = explode('T', $dvalue['ends_at'])[0];

            if ($end_date <= $current_date) {
                $final_data[] = [
                    "departure_id" => $dvalue['id'],
                    "starts_at"    => $dvalue['starts_at'],
                    "ends_at"      => $dvalue['ends_at'],
                ];
            }
        }

        return [
            "status" => "success",
            "msg"    => "ok",
            "data"   => $final_data,
        ];
    }

    public function participants(Request $request)
    {
        $departure_id = $request->input('departure_id');

        $status_arr = array(
            "confirmed"            => "Confirmed",
            "pending_confirmation" => "Pending Confirmation",
            "pending_inquiry"      => "Pending Inquiry",
            "cancelled"            => "Cancelled",
            "rejected"             => "Rejected",
            "complete"             => "Unconfirmed",
            "incomplete"           => "Incomplete",
            "cart_abandoned"       => "Cart Abandoned",
        );
        $res = $this->client->request('GET', $this->api_url . '/api/v1/admin/departures/' . $departure_id . '?ac_api_key=' . $this->api_key . '&user_secret=' . $this->user_secrete . '&include_booking_custom_forms=all&include_bookings=true&include_trip=true');

        $data = json_decode($res->getBody(), true);
        //print_r($data);
        $final_data = array();

        foreach ($data['bookings'] as $dvalue) {

            $booking_status  = isset($dvalue['state']) ? $status_arr[$dvalue['state']] : "";
            $booking_ref_url = $this->api_url . '/admin/itineraries#/bookings/' . $dvalue['id'] . '/edit';

            if (!empty($dvalue['primary_contact_person'])) {

                $name_pri      = isset($dvalue['primary_contact_person']['full_name']) ? $dvalue['primary_contact_person']['full_name'] : "";
                $phone_pri     = isset($dvalue['primary_contact_person']['phone']) ? $dvalue['primary_contact_person']['phone'] : "";
                $phone_alt_pri = isset($dvalue['primary_contact_person']['phone_alt']) ? $dvalue['primary_contact_person']['phone_alt'] : "";
                //primary phoneno data
                $final_data[] = [
                    "booking_id"        => $dvalue['booking_ref'],
                    "booking_ref_url"   => $booking_ref_url,
                    "passenger_name"    => $name_pri,
                    "primary"           => true,
                    "phone_no"          => $phone_pri,
                    "phone_type"        => '',
                    "booking_status"    => $booking_status,
                    "redundant_contact" => false,
                ];

                //alt phoneno -data
                if ($phone_alt_pri != '') {

                    $final_data[] = [
                        "booking_id"        => $dvalue['booking_ref'],
                        "booking_ref_url"   => $booking_ref_url,
                        "passenger_name"    => $name_pri,
                        "primary"           => true,
                        "phone_no"          => $phone_pri,
                        "phone_type"        => '',
                        "booking_status"    => $booking_status,
                        "redundant_contact" => true,
                    ];
                }

            }
            if (!empty($dvalue['passengers'])) {

                foreach ($dvalue['passengers'] as $passenger_val) {

                    if (!empty($passenger_val['custom_form_values'])) {

                        foreach ($passenger_val['custom_form_values'] as $cus_key => $cus_value) {

                            if (strpos($cus_key, 'vl_full_name_with_title') !== false) {

                                $first_name_other = isset($cus_value['first_name']) ? $cus_value['first_name'] : "";
                                $last_name_other  = isset($cus_value['last_name']) ? $cus_value['last_name'] : "";

                                $final_data[] = [
                                    "booking_id"        => $dvalue['booking_ref'],
                                    "booking_ref_url"   => $booking_ref_url,
                                    "passenger_name"    => $first_name_other . '' . $last_name_other,
                                    "primary"           => false,
                                    "phone_no"          => "914521258442",
                                    "phone_type"        => '',
                                    "booking_status"    => $booking_status,
                                    "redundant_contact" => '',
                                ];
                            }
                        }
                    }
                }
            }
        }

        return [
            "status" => "success",
            "msg"    => "ok",
            "data"   => $final_data,
        ];

    }

}
