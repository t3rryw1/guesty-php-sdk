<?php

namespace Cozy\Lib\Guesty;


class GuestyClient extends UpdatableTokenClient implements IUpdatableTokenClient
{
    public const BASE_URL = "https://open-api.guesty.com";

    public const AUTH_TOKEN_URL = ["POST", "/oauth2/token"];
    public const NEW_RESERVATION_URL = ["POST", "/v1/reservations"];
    public const RETRIEVE_RESERVATION = ["GET", "/v1/reservations/{reservationId}"];
    public const GET_LISTING_URL = ["GET", "/v1/listings/{listingId}"];
    public const BATCH_LISTING_CALENDARS = ["GET", "/v1/availability-pricing/api/calendar/listings"];
    public const UPDATE_LISTING_CALENDARS = ["PUT", "/v1/availability-pricing/api/calendar/listings/{listingId}"];
    public const UPDATE_MULTIPLE_LISTING_CALENDARS = ["PUT", "/v1/availability-pricing/api/calendar/listings"];
    public const LISTINGS = ["GET", "/v1/listings"];
    public const UPDATE_LISTING_INFO = ["PUT", "/v1/listings/{listingId}"];
    public const GUESTS = ["GET", "/v1/guests"];
    public const GUEST_DETAIL = ["GET", "/v1/guests/{guestId}"];
    public const CONVERSATIONS = ["GET", "/v1/communication/conversations"];
    public const CONVERSATION_DETAIL = ["GET", "/v1/communication/conversations/{conversationId}/posts"];
    public const NEW_MESSAGE = ["POST", "/v1/communication/conversations/{conversationId}/send-message"];
    public const UPDATE_CONVERSATION = ["PUT", "/v1/owner-inbox/conversations/{conversationId}"];
    public const UPDATE_RESERVATION = ["PUT", "/v1/reservations/{reservationId}"];

    protected $token;
    private $client_secret;
    private $client_id;
    private $header;
    private $expiresAt;

    public function __construct($client_id, $client_secret, $token = null, $expiresAt = null)
    {
        parent::__construct(self::BASE_URL);
        $this->header = array(
            "Accept: application/json",
            "Authorization: Bearer $token"
        );
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->expiresAt = $expiresAt;
    }

    function fetchNewToken()
    {
        $auth_header = array(
            "accept: application/json"
        );
        $auth_data = [
            'grant_type' => 'client_credentials',
            'scope' => 'open-api',
            'client_secret' => $this->client_secret,
            'client_id' => $this->client_id
        ];
        return $this->request(
            self::AUTH_TOKEN_URL,
            $auth_header,
            $auth_data,
            false
        );
    }

    function isRequestTokenExpired($response)
    {
        // TODO: Implement isRequestTokenExpired() method.
    }

    /**
     * @param $data
     * @param null
     * @return array|mixed|null
     */
    public function newReservation($data)
    {
        return $this->request(
            self::NEW_RESERVATION_URL,
            $this->header,
            $data,
        );
    }

    public function retrieveReservation($reservationId)
    {
        return $this->request(
            self::RETRIEVE_RESERVATION,
            $this->header,
            compact("reservationId")
        );
    }

    public function updateReservation($data)
    {
        return $this->request(
            self::UPDATE_RESERVATION,
            $this->header,
            $data
        );
    }


    public function newListing($data)
    {
        //TODO: implementation
    }

    public function updateListing($data)
    {
        return $this->request(
            self::UPDATE_LISTING_INFO,
            $this->header,
            $data
        );
    }

    public function getListing($listingId)
    {
        return $this->request(
            self::GET_LISTING_URL,
            $this->header,
            compact("listingId"),
        );
    }


    public function newGuest($data)
    {
        //TODO: implementation
    }


    /**
     * @param $listingId
     * @param $startDate
     * @param $endDate
     * @param $status
     * @param $note
     * @param $price
     * @param $minNights
     * @return array|mixed|null
     */
    public function updateListingCalendar(
        $listingId,
        $startDate,
        $endDate,
        $status,
        $note,
        $price,
        $minNights
    )
    {
        $price = intval($price);
        $data = array_filter(compact('listingId', 'startDate', 'endDate', 'status', 'price', 'minNights'));
        isset($note) && $data['note'] = $note;
        return $this->request(
            self::UPDATE_LISTING_CALENDARS,
            $this->header,
            $data
        );
    }

    /**
     * @param $data
     * @return array|mixed|null
     */
    public function updateMultipleListingCalendar($data)
    {
        return $this->request(
            self::UPDATE_MULTIPLE_LISTING_CALENDARS,
            $this->header,
            $data
        );
    }

    /**
     * @param array $guestyIds
     * @param string $startDate
     * @param string $endDate
     * @param null
     * @return array|mixed|null
     */
    public function getMultipleListingCalendars($guestyIds, $startDate, $endDate)
    {
        $res = $this->request(
            self::BATCH_LISTING_CALENDARS,
            $this->header,
            [
                "listingIds" => implode(",", $guestyIds),
                "startDate" => $startDate,
                "endDate" => $endDate
            ],
        );
        return $res['data']['days'];
    }

    public function getListingCount()
    {
        $res = $this->request(
            self::LISTINGS,
            $this->header,
            [
                "fields" => "_id",
                "limit" => 1
            ]
        );

        return $res['count'];
    }

    public function getListings($skip, $limit, $ids = null)
    {
        if ($skip === 0) {
            if ($ids != null) {
                $idStr = implode(",", $ids);
                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->header,
                    [
                        "ids" => $idStr,
                        "limit" => $limit
                    ],
                    'results',
                );
            } else {
                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->header,
                    ["limit" => $limit],
                    'results',
                );
            }
        } else {
            if ($ids != null) {
                $idStr = implode(",", $ids);

                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->header,
                    [
                        "ids" => $idStr,
                        "limit" => $limit,
                        "skip" => $skip],
                    'results',
                );
            } else {
                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->header,
                    ["limit" => $limit, "skip" => $skip],
                    'results',
                );
            }
        }

        return $result;
    }

    public function getGuestCount()
    {
        $res = $this->request(
            self::GUESTS,
            $this->header,
            [
                "fields" => "_id",
            ]
        );

        return $res['count'];
    }

    public function getGuests(int $skip, $limit)
    {
        if ($skip === 0) {
            $result = $this->requestArray(
                self::GUESTS,
                $this->header,
                ["limit" => $limit],
                'results'
            );
        } else {
            $result = $this->requestArray(
                self::GUESTS,
                $this->header,
                ["limit" => $limit, "skip" => $skip],
                'results'
            );
        }

        return $result;
    }

    public function getGuest($guestId)
    {
        $result = $this->request(
            self::GUEST_DETAIL,
            $this->header,
            compact('guestId')
        );

        return $result;
    }

    /**
     *
     * @param $cursor : cursor, for fetching data after the cursor
     * @param $limit : size of returning list
     * @param $sortParam : the field to sort by
     * @param $dataParams : the fields to return
     * @return mixed|null
     */
    public function getConversations($cursorAfter, $limit, $sort, $fields)
    {
        $result = $this->requestArray(
            self::CONVERSATIONS,
            $this->header,
            array_filter(compact("limit", "sort", "cursorAfter", "fields")),
            'data'
        );

        return $result['data'];
    }

    /**
     * @param $conversationId
     * @param $cursorAfter
     * @param $limit
     * @param $sort
     * @return array|mixed|null
     */
    public function getConversationDetail($conversationId, $cursorAfter, $limit, $sort)
    {
        $result = $this->request(
            self::CONVERSATION_DETAIL,
            $this->header,
            array_filter(compact('conversationId', 'cursorAfter', 'limit', 'sort'))
        );

        return $result['data'];
    }

    /**
     * @param $data
     * @return array|mixed|null
     */
    public function newMessage($data)
    {
        return $this->request(
            self::NEW_MESSAGE,
            $this->header,
            $data
        );
    }

    /**
     * @param $data
     * @return array|mixed|null
     */
    public function updateConversation($data)
    {
        return $this->request(
            self::UPDATE_CONVERSATION,
            $this->header,
            $data
        );
    }
}
