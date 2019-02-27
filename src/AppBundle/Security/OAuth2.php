<?php
/**
 * Created by PhpStorm.
 * Date: 07.02.18
 * Time: 17:01
 */

namespace AppBundle\Security;

use AppBundle\Entity\User;
use OAuth2\IOAuth2GrantCode;
use OAuth2\IOAuth2GrantUser;
use OAuth2\IOAuth2RefreshTokens;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2 as OAuth2Base;
use OAuth2\OAuth2ServerException;

class OAuth2 extends OAuth2Base
{
    /**
     * @param IOAuth2Client $client
     * @param mixed $data
     * @param null $scope
     * @param null $access_token_lifetime
     * @param bool $issue_refresh_token
     * @param null $refresh_token_lifetime
     * @return array
     */
    public function createAccessToken(IOAuth2Client $client, $data, $scope = null, $access_token_lifetime = null, $issue_refresh_token = true, $refresh_token_lifetime = null)
    {
        $token = array(
            "access_token" => $this->genAccessToken(),
            "expires_in" => ($access_token_lifetime ?: $this->getVariable(self::CONFIG_ACCESS_LIFETIME)),
            "role" => ($data instanceof User) ? (isset($data->getRoles()[0])) ? $data->getRoles()[0] : null : null,
            "id" => ($data instanceof User) ? $data->getId() : null
        );

        $this->storage->createAccessToken(
            $token["access_token"],
            $client,
            $data,
            time() + ($access_token_lifetime ?: $this->getVariable(self::CONFIG_ACCESS_LIFETIME)),
            $scope
        );

        // Issue a refresh token also, if we support them
        if ($this->storage instanceof IOAuth2RefreshTokens && $issue_refresh_token === true) {
            $token["refresh_token"] = $this->genAccessToken();
            $this->storage->createRefreshToken(
                $token["refresh_token"],
                $client,
                $data,
                time() + ($refresh_token_lifetime ?: $this->getVariable(self::CONFIG_REFRESH_LIFETIME)),
                $scope
            );

            // If we've granted a new refresh token, expire the old one
            if (null !== $this->oldRefreshToken) {
                $this->storage->unsetRefreshToken($this->oldRefreshToken);
                $this->oldRefreshToken = null;
            }
        }

        if ($this->storage instanceof IOAuth2GrantCode) {
            if (null !== $this->usedAuthCode) {
                $this->storage->markAuthCodeAsUsed($this->usedAuthCode->getToken());
                $this->usedAuthCode = null;
            }
        }

        return $token;
    }

    /**
     * @param IOAuth2Client $client
     * @param array $input
     * @return array|bool
     * @throws OAuth2ServerException
     */
    public function grantAccessTokenUserCredentials(IOAuth2Client $client, array $input)
    {
        if (!($this->storage instanceof IOAuth2GrantUser)) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_UNSUPPORTED_GRANT_TYPE);
        }

        if (!$input["username"] || !$input["password"]) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');
        }

        $stored = $this->storage->checkUserCredentials($client, $input["username"], $input["password"]);

        if ($stored === false) {
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_GRANT, "Combination of Username and Password is not correct. Please try again.");
        }

        if($stored['data'] instanceof User){
            if(!$stored['data']->isEnabled()){
                if($stored['data']->getApproved() === NULL){
                    throw new OAuth2ServerException(self::HTTP_FORBIDDEN, 'user_awaiting_approval', "User Awaiting approval");
                }
                else{
                    throw new OAuth2ServerException(self::HTTP_FORBIDDEN, 'user_deactivate', "User Deactivate");
                }
            }
        }
        else{
            throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_GRANT, "Combination of Username and Password is not correct. Please try again.");
        }

        return $stored;
    }
}