<?php

namespace App\Services;


class ResponseService
{
    protected $responseCode;
    protected $status;
    protected $message;
    protected $data;

    private function responseWrapperPagination()
    {
        return [
            'code'    => $this->responseCode ?? 200,
            'status' => $this->status ?? 'success',
            'message' => $this->message ?? 'Request processed successfully',
            'data'    => $this->data ?? null,
            'pagination' => $this->paginationDetails($this->data) ?? null
        ];
    }

    private function responseWrapper()
    {
        return [
            'code'    => $this->responseCode ?? 200,
            'status' => $this->status ?? 'success',
            'message' => $this->message ?? 'Request processed successfully',
            'data'    => $this->data ?? null,
        ];
    }

    private function paginationDetails($paginator)
    {
        return [
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
            'total_items' => $paginator->total(),
            'per_page' => $paginator->perPage(),
        ];
    }

    public function createdJsonResponse($httpResponseCode = 201, $status = null, $message = null, $data = [], array $headers = [])
    {
        $this->responseCode = $httpResponseCode;
        $this->status = $status ?? 'created';
        $this->message = $message ?? 'Request processed successfully';
        $this->data = $data;

        return response()->json($this->responseWrapper(), $this->responseCode, $headers);
    }

    public function updatedJsonResponse($httpResponseCode = 200, $status = null, $message = null, $data = [], array $headers = [])
    {
        $this->responseCode = $httpResponseCode;
        $this->status = $status ?? 'updated';
        $this->message = $message ?? 'Request processed successfully';
        $this->data = $data;

        return response()->json($this->responseWrapper(), $this->responseCode, $headers);
    }

    public function listJsonResponse($httpResponseCode = 200, $status = null, $message = null, $data = [], array $headers = [])
    {
        $this->responseCode = $httpResponseCode;
        $this->status = $status ?? 'success';
        $this->message = $message ?? 'Request processed successfully';
        $this->data = $data;

        return response()->json($this->responseWrapperPagination(), $this->responseCode, $headers);
    }

    public function successJsonResponse($httpResponseCode = 200, $status = null, $message = null, $data = [], array $headers = [])
    {
        $this->responseCode = $httpResponseCode;
        $this->status = $status ?? 'success';
        $this->message = $message ?? 'Request processed successfully';
        $this->data = $data ?? null;

        return response()->json($this->responseWrapper(), $this->responseCode, $headers);
    }

    public function errorListJsonResponse($httpResponseCode = 500, $status = null, $message = null, array $headers = [])
    {
        $this->responseCode = $httpResponseCode;
        $this->status = $status ?? 'error';
        $this->message = $message ?? 'Request processed successfully';

        return response()->json(
            [
                'code'    => $this->responseCode,
                'status' => 'error',
                'message' => $this->message,
                'data'    => [],
                'pagination' => []
            ]
        ,$this->responseCode);
    }

    public function errorJsonResponse($httpResponseCode = 500, $status = null, $message = null,)
    {
        $this->responseCode = $httpResponseCode;
        $this->status = $status ?? 'error';
        $this->message = $message ?? 'Request processed successfully';

        return response()->json(
            [
                'code'    => $this->responseCode,
                'status' => 'error',
                'message' => $this->message
            ]
        , $this->responseCode);
    }
}
