<?php

namespace Cozy\Lib\Guesty;


class GuestyClient extends AbstractClient
{
    public const BASE_URL = "https://api.guesty.com/api/v2/";

    public const NEW_RESERVATION_URL = ["POST", "reservations"];
    public const RETRIEVE_RESERVATION = ["GET", "/reservations/{reservationId}"];
    public const GET_LISTING_URL = ["GET", "listings/{listingId}"];
    public const BATCH_LISTING_CALENDARS = ["GET", "availability-pricing/api/calendar/listings"];
    public const UPDATE_LISTING_CALENDARS = ["PUT", "availability-pricing/api/calendar/listings/{listingId}"];
    public const UPDATE_MULTIPLE_LISTING_CALENDARS = ["PUT", "availability-pricing/api/calendar/listings"];
    public const LISTINGS = ["GET", "listings"];
    public const UPDATE_LISTING_INFO = ["PUT", "listings/{listingId}"];
    public const GUESTS = ["GET", "guests"];
    public const GUEST_DETAIL = ["GET", "guests/{guestId}"];
    public const CONVERSATIONS = ["GET", "communication/conversations"];
    public const CONVERSATION_DETAIL = ["GET", "/communication/conversations/{conversationId}/posts"];
    public const NEW_MESSAGE = ["POST", "/communication/conversations/{conversationId}/send-message"];
    public const UPDATE_CONVERSATION = ["PUT", "owner-inbox/conversations/{conversationId}"];
    public const UPDATE_RESERVATION = ["PUT", "/reservations/{reservationId}"];

    private $token;

    public function __construct($token)
    {
        parent::__construct(self::BASE_URL);
        $this->token = array(
            "Authorization: Basic $token",
            "Content-Type: application/json"
        );
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
            $this->token,
            $data,
        );
    }

    public function retrieveReservation($reservationId)
    {
        return $this->request(
            self::RETRIEVE_RESERVATION,
            $this->token,
            compact("reservationId")
        );
    }

    public function updateReservation($data)
    {
        return $this->request(
            self::UPDATE_RESERVATION,
            $this->token,
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
            $this->token,
            $data
        );
    }

    public function getListing($listingId)
    {
        return $this->request(
            self::GET_LISTING_URL,
            $this->token,
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
    ) {
        $price = intval($price);
        $data = array_filter(compact('listingId', 'startDate', 'endDate', 'status', 'price', 'minNights'));
        isset($note) && $data['note'] = $note;
        return $this->request(
            self::UPDATE_LISTING_CALENDARS,
            $this->token,
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
            $this->token,
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
        $res= $this->request(
            self::BATCH_LISTING_CALENDARS,
            $this->token,
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
            $this->token,
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
                    $this->token,
                    [
                        "ids" => $idStr,
                        "limit" => $limit
                    ],
                    'results',
                );
            } else {
                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->token,
                    ["limit" => $limit],
                    'results',
                );
            }
        } else {
            if ($ids != null) {
                $idStr = implode(",", $ids);

                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->token,
                    [
                        "ids" => $idStr,
                        "limit" => $limit,
                        "skip" => $skip],
                    'results',
                );
            } else {
                $result = $this->requestArray(
                    self::LISTINGS,
                    $this->token,
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
            $this->token,
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
                $this->token,
                ["limit" => $limit],
                'results'
            );
        } else {
            $result = $this->requestArray(
                self::GUESTS,
                $this->token,
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
            $this->token,
            compact('guestId')
        );

        return $result;
    }

    /**
     *
     * @param $cursor: cursor, for fetching data after the cursor
     * @param $limit: size of returning list
     * @param $sortParam: the field to sort by
     * @param $dataParams: the fields to return
     * @return mixed|null
     */
    public function getConversations($cursorAfter, $limit, $sort, $fields)
    {
        $result = $this->requestArray(
            self::CONVERSATIONS,
            $this->token,
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
            $this->token,
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
            $this->token,
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
            $this->token,
            $data
        );
    }
}
