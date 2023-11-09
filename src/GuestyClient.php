<?php

namespace Cozy\Lib\Guesty;

class GuestyClient extends UpdatableTokenClient implements IUpdatableTokenClient
{
    public const BASE_URL = "https://open-api.guesty.com";

    public const AUTH_TOKEN_URL = ["POST", "/oauth2/token"];
    public const NEW_RESERVATION_URL = ["POST", "/v1/reservations"];
    public const RETRIEVE_RESERVATION = ["GET", "/v1/reservations/{reservationId}"];
    public const RETRIEVE_RESERVATION_LIST = ["GET", "/v1/reservations"];
    public const NEW_LISTING_URL = ["POST", "/v1/listings"];
    public const GET_LISTING_URL = ["GET", "/v1/listings/{listingId}"];
    public const BATCH_LISTING_CALENDARS = ["GET", "/v1/availability-pricing/api/calendar/listings"];
    public const UPDATE_LISTING_CALENDARS = ["PUT", "/v1/availability-pricing/api/calendar/listings/{listingId}"];
    public const UPDATE_MULTIPLE_LISTING_CALENDARS = ["PUT", "/v1/availability-pricing/api/calendar/listings"];
    public const LISTINGS = ["GET", "/v1/listings"];
    public const UPDATE_LISTING_INFO = ["PUT", "/v1/listings/{listingId}"];
    public const UPDATE_LISTING_AVAIL_SETTING = ["PUT","/v1/listings/{listingId}/availability-settings"];
    public const DELETE_LISTING = ["DELETE","/v1/listings/{listingId}"];
    public const GUESTS = ["GET", "/v1/guests"];
    public const GUEST_DETAIL = ["GET", "/v1/guests/{guestId}"];
    public const CONVERSATIONS = ["GET", "/v1/communication/conversations"];
    public const CONVERSATION_DETAIL = ["GET", "/v1/communication/conversations/{conversationId}/posts"];
    public const NEW_MESSAGE = ["POST", "/v1/communication/conversations/{conversationId}/send-message"];
    public const UPDATE_CONVERSATION = ["PUT", "/v1/owner-inbox/conversations/{conversationId}"];
    public const UPDATE_RESERVATION = ["PUT", "/v1/reservations/{reservationId}"];
    public const ADD_LISTING_SPACE = ["POST", "/v1/properties/spaces/unit-type/{unitTypeId}/add"];
    public const RETRIEVE_LISTING_SPACE = ["GET","/v1/properties/spaces/unit-type/{unitTypeId}"];
    public const DELETE_LISTING_SPACE = ["POST","/v1/properties/spaces/space/{spaceId}/remove"];
    public const EDIT_LISTING_SPACE = ["POST","/v1/properties/spaces/space/{spaceId}/edit"];
    public const APPROVE_PENDING_RESERVATION = ["POST","/v1/reservations/{reservationId}/approve"];
    public const DECLINE_PENDING_RESERVATION = ["POST","/v1/reservations/{reservationId}/decline"];
    public const LIST_USER = ["GET","/v1/users"];
    public const GET_USER = ["GET","/v1/users/{id}"];
    public const UPDATE_USER = ["PUT","/v1/users/{id}"];
    public const CREATE_USER = ["POST","/v1/users"];
    public const UPDATE_RESERVATION_CUSTOM_FIELD = ["PUT","/v1/reservations/{id}/custom-fields"];
    public const RETRIEVE_HOUSE_RULES = ["GET","/v1/properties/house-rules/unit-type/{listingId}"];
    public const UPDATE_HOUSE_RULES = ["PUT","/v1/properties/house-rules/unit-type/{listingId}"];
    public const GET_SAVED_REPLIES = ["GET","/v1/saved-replies"];

    protected $token;
    private $clientSecret;
    private $clientId;

    public function __construct(
        $clientId,
        $clientSecret,
        $client,
        $token = null,
        $expiresAt = null
    ) {
        parent::__construct($client, $token, $expiresAt);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function fetchNewToken(): array
    {
        $authHeader = [
            "Accept: application/json"
        ];
        $authData = [
            'grant_type' => 'client_credentials',
            'scope' => 'open-api',
            'client_secret' => $this->clientSecret,
            'client_id' => $this->clientId
        ];
        $res = $this->client->request(
            self::AUTH_TOKEN_URL,
            $authHeader,
            $authData,
            false,
            false
        );
        $responseCode = $this->client->getLastResponseCode();
        $this->throwException($responseCode);
        return [
            $res['access_token'],
            $res['expires_in']
        ];
        // we need to parse result here and return token.
    }

    public function isRequestTokenExpired($response): bool
    {
        return $this->client->getLastResponseCode() === 401;
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
            $data
        );
    }

    public function retrieveReservation($reservationId)
    {
        return $this->optimisticRequestWithToken(
            self::RETRIEVE_RESERVATION,
            compact("reservationId")
        );
    }

    public function retrieveReservationList($skip, $limit)
    {
        if ($skip === 0) {
            $result = $this->optimisticRequestWithToken(
                self::RETRIEVE_RESERVATION_LIST,
                ["limit" => $limit]
            );
        } else {
            $result = $this->optimisticRequestWithToken(
                self::RETRIEVE_RESERVATION_LIST,
                ["limit" => $limit, "skip" => $skip]
            );
        }
        return $result['results'];
    }

    public function updateReservation($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_RESERVATION,
            $data
        );
    }

    public function updateReservationCustomField($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_RESERVATION_CUSTOM_FIELD,
            $data
        );
    }

    public function newListing($data)
    {
        return $this->optimisticRequestWithToken(
            self::NEW_LISTING_URL,
            $data
        );
    }

    public function updateListing($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_LISTING_INFO,
            $data
        );
    }

    public function updateListingSetting($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_LISTING_AVAIL_SETTING,
            $data
        );
    }

    public function addListingSpace($data)
    {
        return $this->optimisticRequestWithToken(
            self::ADD_LISTING_SPACE,
            $data
        );
    }

    public function retrieveListingSpace($unitTypeId)
    {
        return $this->optimisticRequestWithToken(
            self::RETRIEVE_LISTING_SPACE,
            compact("unitTypeId")
        );
    }

    public function deleteListingSpace($data)
    {
        return $this->optimisticRequestWithToken(
            self::DELETE_LISTING_SPACE,
            $data
        );
    }

    public function editListingSpace($data)
    {
        return $this->optimisticRequestWithToken(
            self::EDIT_LISTING_SPACE,
            $data
        );
    }


    public function deleteListing($data)
    {
        return $this->optimisticRequestWithToken(
            self::DELETE_LISTING,
            $data
        );
    }

    public function getListing($listingId)
    {
        return $this->optimisticRequestWithToken(
            self::GET_LISTING_URL,
            compact("listingId")
        );
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
        $res = $this->optimisticRequestWithToken(
            self::BATCH_LISTING_CALENDARS,
            [
                "listingIds" => implode(",", $guestyIds),
                "startDate" => $startDate,
                "endDate" => $endDate
            ]
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
                    ]
                );
            } else {
                $result = $this->optimisticRequestWithToken(
                    self::LISTINGS,
                    ["limit" => $limit]
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
                        "skip" => $skip]
                );
            } else {
                $result = $this->optimisticRequestWithToken(
                    self::LISTINGS,
                    ["limit" => $limit, "skip" => $skip]
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
                ["limit" => $limit]
            );
        } else {
            $result = $this->optimisticRequestWithToken(
                self::GUESTS,
                ["limit" => $limit, "skip" => $skip]
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

    public function approvePendingReservation($data)
    {
        return $this->optimisticRequestWithToken(
            self::APPROVE_PENDING_RESERVATION,
            $data
        );
    }

    public function declineReservation($data)
    {
        return $this->optimisticRequestWithToken(
            self::DECLINE_PENDING_RESERVATION,
            $data
        );
    }

    public function getUser($id)
    {
        return $this->optimisticRequestWithToken(
            self::GET_USER,
            compact('id')
        );
    }

    public function listUser(int $skip, $limit)
    {
        if ($skip === 0) {
            $result = $this->optimisticRequestWithToken(
                self::LIST_USER,
                ["limit" => $limit]
            );
        } else {
            $result = $this->optimisticRequestWithToken(
                self::LIST_USER,
                ["limit" => $limit, "skip" => $skip]
            );
        }

        return $result['results'];
    }

    public function updateUser($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_USER,
            $data
        );
    }
    
    public function createUser($data)
    {
        return $this->optimisticRequestWithToken(
            self::CREATE_USER,
            $data
        );
    }

    public function getHouseRule($listingId)
    {
        return $this->optimisticRequestWithToken(
            self::RETRIEVE_HOUSE_RULES,
            compact("listingId")
        );
    }

    public function updateHouseRule($data)
    {
        return $this->optimisticRequestWithToken(
            self::UPDATE_HOUSE_RULES,
            $data
        );
    }

    public function listReplies(int $skip, $limit)
    {
        if ($skip === 0) {
            $result = $this->optimisticRequestWithToken(
                self::GET_SAVED_REPLIES,
                ["limit" => $limit]
            );
        } else {
            $result = $this->optimisticRequestWithToken(
                self::GET_SAVED_REPLIES,
                ["limit" => $limit, "skip" => $skip]
            );
        }

        return $result['results'];
    }
}
