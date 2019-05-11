<?php

declare(strict_types=1);
trait DataHelper
{
    private function getData($endpoint)
    {
        return $this->checkResult(json_decode($this->SendDataToParent(json_encode([
            'DataID'   => '{1E587107-664D-BA29-59E0-D9167875BE7E}',
            'Endpoint' => $endpoint,
            'Payload'  => ''
        ]))));
    }

    private function postData($endpoint, $payload = '{}')
    {
        return $this->checkResult(json_decode($this->SendDataToParent(json_encode([
            'DataID'   => '{1E587107-664D-BA29-59E0-D9167875BE7E}',
            'Endpoint' => $endpoint,
            'Payload'  => $payload
        ]))));
    }

    private function checkResult($result)
    {
        if (isset($result->errorCode)) {
            throw new Exception($result->errorCode);
        }

        return $result;
    }
}
