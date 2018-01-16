<?php

namespace Site\Storage;

interface UserMapperInterface
{
    /**
     * Fetches by credentials
     * 
     * @param string $login
     * @param string $password
     * @return array
     */
    public function fetchByCredentials(string $login, string $password);
}
