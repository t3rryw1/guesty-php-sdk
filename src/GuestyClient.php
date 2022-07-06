<?php

namespace Cozy\Lib\Guesty;

use JetBrains\PhpStorm\NoReturn;

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
    private $clientSecret;
    private $clientId;

    public function __construct(
        $clientId,
        $clientSecret,
        $client,
        $token = null, 
        $expiresAt = null)
    {
        parent::__construct($client,$token,$expiresAt);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    function fetchNewToken():array
    {
        $authHeader = array(
            "accept: application/json"
        );
        $authData = [
            'grant_type' => 'client_credentials',
            'scope' => 'open-api',
            'client_secret' => $this->clientSecret,
            'client_id' => $this->clientId
        ];
        $res= $this->client->request(
            self::AUTH_TOKEN_URL,
            $authHeader,
            $authData,
            false
        );
        return [$res['access_token'],$res['expires_in']];
        // we need to parse result here and return token.
    }

    function isRequestTokenExpired($response):bool
    {
        return true;
        // TODO: Implement isRequestTokenExpired() method.
    }

    /**
     * @param $data
     * @param null
     * @return array|mixed|null
     */
    public function newReservation($data)
    {
        return $this->optimisticRequestWithToken(
            self::NEW_RESERVATION_URL,
            $data,
        );
    }

    public function retrieveReservation($reservationId)
    {
        return $this->optimisticRequestWithToken(
            self::RETRIEVE_RESERVATION,
            compact("reservationId")
        );
    }

    public function updateReservation($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_RESERVATION,
            $data
        );
    }


    public function newListing($data)
    {
        //TODO: implementation
    }

    public function updateListing($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_LISTING_INFO,
            $data
        );
    }

    public function getListing($listingId)
    {
        return $this->optimisticRequestWithToken(
            self::GET_LISTING_URL,
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
        return $this->optimisticRequestWithToken(
            self::UPDATE_LISTING_CALENDARS,
            $data
        );
    }

    /**
     * @param $data
     * @return array|mixed|null
     */
    public function updateMultipleListingCalendar($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_MULTIPLE_LISTING_CALENDARS,
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
        $res= $this->optimisticRequestWithToken(
            self::BATCH_LISTING_CALENDARS,
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
        $res = $this->optimisticRequestWithToken(
            self::LISTINGS,
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
                $result = $this->optimisticRequestWithToken(
                    self::LISTINGS,
                    [
                        "ids" => $idStr,
                        "limit" => $limit
                    ],
                );
            } else {
                $result = $this->optimisticRequestWithToken(
                    self::LISTINGS,
                    ["limit" => $limit],
                    'results',
                );
            }
        } else {
            if ($ids != null) {
                $idStr = implode(",", $ids);

                $result = $this->optimisticRequestWithToken(
                    self::LISTINGS,
                    [
                        "ids" => $idStr,
                        "limit" => $limit,
                        "skip" => $skip],
                );
            } else {
                $result = $this->optimisticRequestWithToken(
                    self::LISTINGS,
                    ["limit" => $limit, "skip" => $skip],
                );
            }
        }

        return $result['results'];
    }

    public function getGuestCount()
    {
        $res = $this->optimisticRequestWithToken(
            self::GUESTS,
            [
                "fields" => "_id",
            ]
        );

        return $res['count'];
    }

    public function getGuests(int $skip, $limit)
    {
        if ($skip === 0) {
            $result = $this->optimisticRequestWithToken(
                self::GUESTS,
                ["limit" => $limit],
            );
        } else {
            $result = $this->optimisticRequestWithToken(
                self::GUESTS,
                ["limit" => $limit, "skip" => $skip],
            );
        }

        return $result['results'];
    }

    public function getGuest($guestId)
    {
        return $this->optimisticRequestWithToken(
            self::GUEST_DETAIL,
            compact('guestId')
        );
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
        $result = $this->optimisticRequestWithToken(
            self::CONVERSATIONS,
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
        $result = $this->optimisticRequestWithToken(
            self::CONVERSATION_DETAIL,
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
        return $this->optimisticRequestWithToken(
            self::NEW_MESSAGE,
            $data
        );
    }

    /**
     * @param $data
     * @return array|mixed|null
     */
    public function updateConversation($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_CONVERSATION,
            $data
        );
    }
}
