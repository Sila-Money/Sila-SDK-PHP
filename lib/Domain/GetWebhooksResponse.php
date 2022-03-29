<?php

/**
 * Get Webhooks Reponse
 * PHP version 7.2
 */

namespace Silamoney\Client\Domain;

use JMS\Serializer\Annotation\Type;
use Pagination;

/**
 * Get Webhooks Reponse
 * Object used to map the get webhooks method response.
 * @category Class
 * @package  Silamoney\Client
 * @author   José Morales <jmorales@digitalgeko.com>
 */
class GetWebhooksResponse
{
    /**
     * Boolean field used for success.
     * @var bool
     * @Type("bool")
     */
    public $success;
    /**
     * @var string
     * @Type("string")
     */
    public $status;
    /**
     * Integer field used for the page.
     * @var int
     * @Type("int")
     */
    public $page;
    /**
     * Integer field used for the returned count.
     * @var int
     * @Type("int")
     */
    public $returnedCount;
    /**
     * Integer field used for the total count.
     * @var int
     * @Type("int")
     */
    public $totalCount;
    /**
     * webhooks list used for the webhooks.
     * @Type("array")
     */
    public $webhooks;
    /**
     * Pagination.
     * @var Silamoney\Client\Domain\Pagination
     * @Type("Silamoney\Client\Domain\Pagination")
     */
    public $pagination;

    /**
     * response_time_ms
     * @var string
     * @Type("string")
     */
    public $response_time_ms;


    /**
     * Checks to see if the request was successful
     * @return bool
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string
    */
    public function getStatus(): string
    {
        return $this->status;
    }
    
    /**
     * Gets the response response_time_ms.
     * @return bool
    */
    public function getResponseTimeMs(): bool
    {
        return $this->response_time_ms;
    }

     /**
     * @return Silamoney\Client\Domain\Pagination
     */
    public function getPagination(): Simanoney\Client\Domain\Pagination
    {
        return $this->pagination;
    }

    public function getTransactionById($id)
    {
        $tx = array_values(array_filter($this->webhooks, function ($v) use ($id) {
            return $v->referenceId = $id;
        }, ARRAY_FILTER_USE_BOTH));
        return count($tx) ? $tx[0] : null;
    }
}
