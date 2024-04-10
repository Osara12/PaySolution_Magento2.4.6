<?php

namespace PaySolutions\Base\Api;

interface PostManagementInterface {

    /**
     * GET for Post api
     * @param string $value
     * @return string
     */
    public function getPost($value);
}