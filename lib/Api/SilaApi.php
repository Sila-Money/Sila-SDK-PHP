<?php

/**
 * Sila Api
 * PHP version 7.2
 */

namespace Silamoney\Client\Api;

use DateTime;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use JMS\Serializer\SerializerBuilder;
use Silamoney\Client\Configuration\Configuration;
use Silamoney\Client\Domain\{
    Account,
    AchType,
    ApiEndpoints,
    AddressMessage,
    BalanceEnvironments,
    BankAccountMessage,
    BaseBusinessMessage,
    BaseMessage,
    BaseResponse,
    BusinessEntityMessage,
    BusinessUser,
    CancelTransactionMessage,
    CertifyBeneficialOwnerMessage,
    CheckHandleResponse,
    CheckPartnerKYCMessage,
    CheckPartnerKYCResponse,
    Country,
    DeleteRegistrationMessage,
    OperationResponse,
    EntityMessage,
    Environments,
    GetAccountBalanceMessage,
    GetAccountBalanceResponse,
    GetAccountsMessage,
    GetInstitutionsMessage,
    GetInstitutionsResponse,
    GetTransactionsMessage,
    GetTransactionsResponse,
    HeaderMessage,
    Institution,
    LinkAccountMessage,
    UpdateAccountMessage,
    DeleteAccountMessage,
    LinkAccountResponse,
    UpdateAccountResponse,
    DeleteAccountResponse,
    Message,
    PlaidTokenType,
    SearchFilters,
    SilaBalanceMessage,
    SilaBalanceResponse,
    TransferMessage,
    User,
    SilaWallet,
    GetWalletMessage,
    RegisterWalletMessage,
    Wallet,
    UpdateWalletMessage,
    DeleteWalletMessage,
    DocumentMessage,
    EmailMessage,
    EntityUpdateMessage,
    GetDocumentMessage,
    GetEntitiesMessage,
    GetWalletsMessage,
    HeaderBaseMessage,
    IdentityAlias,
    IdentityMessage,
    LinkBusinessMemberMessage,
    ListDocumentsMessage,
    PhoneMessage,
    RegisterResponse,
    RegistrationDataOperation,
    RegistrationDataType,
    TransferResponse,
    UnlinkBusinessMemberMessage,
    LinkCardMessage,
    LinkCardResponse,
    GetCardsMessage,
    DeleteCardMessage,
    DeleteCardResponse,
    ReverseTransactionMessage,
    GetWebhooksMessage,
    GetWebhooksResponse,
    RetryWebhookMessage,
    GetPaymentMethodsMessage,
    VirtualAccountMessage,
    VirtualAccountResponse,
    GetVirtalAccountMessage,
    UpdateVirtualAccountMessage,
    CloseVirtualAccountMessage,
    RequestKYCResponse,
    CreateTestVirtualAccountAchTransactionMessage,
    DocumentListMessage,
    DocumentWithoutHeaderMessage,
    LinkAccountMxMessage,
    GetStatementsMessage,
    GetStatementsResponse,
    Statements,
    StatementsMessage,
    ResendStatementsMessage,
    CreateCKOTestingTokenMessage,
    CreateCKOTestingTokenResponse,
    RefundDebitCardMessage,
    RefundDebitCardResponse
};
use Silamoney\Client\Security\EcdsaUtil;

/**
 * Sila Api
 *
 * @category Class
 * @package Silamoney\Client
 * @author José Morales <jmorales@digitalgeko.com>
 */
class SilaApi
{

    /**
     *
     * @var \Silamoney\Client\Configuration\Configuration
     */
    private $configuration;

    /**
     *
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     *
     * @var string
     */
    private const AUTH_SIGNATURE = "authsignature";

    /**
     *
     * @var string
     */
    private const USER_SIGNATURE = 'usersignature';

    /**
     * @var string
     */
    private const BUSINESS_SIGNATURE = 'businesssignature';

    /**
     *
     * @var string
     */
    private const DEFAULT_ENVIRONMENT = Environments::SANDBOX;

    /**
     * @var string
     */
    private const DEFAULT_BALANCE_ENVIRONMENT = BalanceEnvironments::SANDBOX;

    /**
     * Constructor for Sila Api using custom environment.
     *
     * @param string $environment
     * @param string $balanceEnvironment
     * @param string $appHandle
     * @param string $privateKey
     * @return \Silamoney\Client\Api\SilaApi
     */
    public function __construct(string $environment, string $balanceEnvironment, string $appHandle, string $privateKey)
    {
        $this->configuration = new Configuration($environment, $balanceEnvironment, $privateKey, $appHandle);
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * Constructor for Sila Api using specified environment.
     *
     * @param \Silamoney\Client\Domain\Environments $environment
     * @param \Silamoney\Client\Domain\BalanceEnvironments $balanceEnvironment
     * @param string $appHandle
     * @param string $privateKey
     * @return \Silamoney\Client\Api\SilaApi
     */
    public static function fromEnvironment(
        Environments $environment,
        BalanceEnvironments $balanceEnvironment,
        string $appHandle,
        string $privateKey
    ): SilaApi {
        return new SilaApi($environment, $balanceEnvironment, $appHandle, $privateKey);
    }

    /**
     * Constructor for Sila Api using sandbox environment.
     *
     * @param string $appHandle
     * @param string $privateKey
     * @return \Silamoney\Client\Api\SilaApi
     */
    public static function fromDefault(string $appHandle, string $privateKey): SilaApi
    {
        return new SilaApi(self::DEFAULT_ENVIRONMENT, self::DEFAULT_BALANCE_ENVIRONMENT, $appHandle, $privateKey);
    }

    /**
     * Checks if a specific handle is already taken.
     *
     * @param string $handle
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function checkHandle(string $handle): ApiResponse
    {
        $body = new HeaderMessage($handle, $this->configuration->getAppHandle());
        $path = ApiEndpoints::CHECK_HANDLE;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareBaseResponse($response);
    }

    /**
     * Attaches KYC data and specified blockchain address to an assigned handle.
     *
     * @param User $user
     * @return ApiResponse
     * @throws ClientException
     */
    public function register(User $user): ApiResponse
    {
        $body = new EntityMessage($user, $this->configuration->getAppHandle());
        $path = ApiEndpoints::REGISTER;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareBaseResponse($response);
    }

    /**
     * Gets a paginated list of payment methods attached to a user handle.
     *
     * @param string $userHandle
     * @param SearchFilters $searchFilters
     * @return ApiResponse
     * @throws Exception
     */
    public function getPaymentMethods(
        string $userHandle,
        string $userPrivateKey,
        SearchFilters $searchFilters = null
    ): ApiResponse {
        $body = new GetPaymentMethodsMessage($userHandle, $this->configuration->getAppHandle(), $searchFilters);
        $path = ApiEndpoints::GET_PAYMENT_METHODS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Opens a new Virtual Account for the given to a verified
     * entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $virtualAccountName
     * @param boolean|null $achDebitEnabled
     * @param boolean|null $achCreditEnabled
     * @return ApiResponse
     */
    public function openVirtualAccount(
        string $userHandle,
        string $userPrivateKey,
        string $virtualAccountName,
        ?bool $achDebitEnabled = null,
        ?bool $achCreditEnabled = null,
        ?bool $statements_enabled = null
    ): ApiResponse {
        $body = new VirtualAccountMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $virtualAccountName,
            $achDebitEnabled,
            $achCreditEnabled,
            $statements_enabled
        );
        $path = ApiEndpoints::OPEN_VIRTUAL_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, VirtualAccountResponse::class);
    }

    /**
     * Gets details about of the user virtual account using virtual account id.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $virtualAccountId
     * @return ApiResponse
     * @throws Exception
     */
    public function getVirtualAccount(string $userHandle, string $userPrivateKey, string $virtualAccountId): ApiResponse
    {
        $body = new GetVirtalAccountMessage($userHandle, $virtualAccountId, $this->configuration->getAppHandle());
        $path = ApiEndpoints::GET_VIRTUAL_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Gets a paginated list of "wallets"/blockchain addresses attached to a user handle.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     */
    /**
     * Gets a paginated list of the user virtual accounts attached to a user handle.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param SearchFilters $searchFilters
     * @return ApiResponse
     * @throws Exception
     */
    public function getVirtualAccounts(
        string $userHandle,
        string $userPrivateKey,
        SearchFilters $searchFilters = null
    ): ApiResponse {
        $body = new GetWalletsMessage($userHandle, $this->configuration->getAppHandle(), $searchFilters);
        $path = ApiEndpoints::GET_VIRTUAL_ACCOUNTS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }
    
    /**
     * Updates nickname and/or default status of a virtual account.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $virtualAccountId
     * @param string $virtualAccountName
     * @param boolean $active
     * @param boolean|null $achDebitEnabled
     * @param boolean|null $achCreditEnabled
     * @return ApiResponse
     * @throws Exception
     */
    public function UpdateVirtualAccount(
        string $userHandle,
        string $userPrivateKey,
        string $virtualAccountId,
        string $virtualAccountName,
        ?bool $statements_enabled,
        ?bool $active = null,
        ?bool $achDebitEnabled = null,
        ?bool $achCreditEnabled = null
    ): ApiResponse 
    {
        $body = new UpdateVirtualAccountMessage($this->configuration->getAppHandle(), $userHandle, $virtualAccountId, $virtualAccountName, $statements_enabled, $active, $achDebitEnabled, $achCreditEnabled);
        $path = ApiEndpoints::UPDATE_VIRTUAL_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Close virtual account.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $virtualAccountId
     * @param string $virtualAccountNumber
     * @return ApiResponse
     * @throws Exception
     */
    public function closeVirtualAccount(
        string $userHandle,
        string $userPrivateKey,
        string $virtualAccountId,
        string $virtualAccountNumber
    ): ApiResponse {
        $body = new CloseVirtualAccountMessage($this->configuration->getAppHandle(), $userHandle, $virtualAccountId, $virtualAccountNumber);
        $path = ApiEndpoints::CLOSE_VIRTUAL_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Creates a test Ach transaction for Virtual Account 
     * entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $virtualAccountNumber
     * @param int $amount
     * @param int $tranCode
     * @param string $entityName
     * @param DateTime|null $effectiveDate 
     * @param string|null $ced
     * @param string|null $achName
     * @return ApiResponse
     */
    public function createTestVirtualAccountAchTransaction(
        string $userHandle,
        string $userPrivateKey,
        string $virtualAccountNumber,
        string $amount,
        string $tranCode,
        string $entityName,
        ?DateTime $effectiveDate = null,
        ?string $ced = null,
        ?string $achName = null
    ): ApiResponse {
        $body = new CreateTestVirtualAccountAchTransactionMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $virtualAccountNumber,
            $amount,
            $tranCode,
            $entityName,
            $effectiveDate,
            $ced,
            $achName
        );
        $path = ApiEndpoints::CREATE_TEST_VIRTUAL_ACCOUNT_ACH_TRANSACTION;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Starts KYC verification process on a registered user handle.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $kycLevel
     * @return ApiResponse
     * @throws Exception
     */
    public function requestKYC(string $userHandle, string $userPrivateKey, string $kycLevel = ''): ApiResponse
    {
        $body = new HeaderMessage($userHandle, $this->configuration->getAppHandle());
        if ($kycLevel != '' && $kycLevel != null) {
            $body->setKycLevel($kycLevel);
        }
        $path = ApiEndpoints::REQUEST_KYC;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, RequestKYCResponse::class);
    }

    /**
     * Returns whether entity attached to user handle is verified, not valid, or still pending.
     *
     * @param string $handle
     * @param string $userPrivateKey
     * @param string|null $kycLevel
     * @return ApiResponse
     * @throws ClientException
     */
    public function checkKYC(string $handle, string $userPrivateKey, string $kycLevel = null): ApiResponse
    {
        $body = new HeaderMessage($handle, $this->configuration->getAppHandle());
        if ($kycLevel != '' && $kycLevel != null) {
            $body->setKycLevel($kycLevel);
        }
        $path = ApiEndpoints::CHECK_KYC;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Uses a provided Plaid public token to link a bank account to a verified
     * entity.
     *
     * @param string $userHandle
     * @param string $publicToken
     * @param string $userPrivateKey
     * @param string|null $accountName
     * @param string|null $accountId
     * @return ApiResponse
     */
    public function linkAccount(
        string $userHandle,
        string $userPrivateKey,
        string $plaidToken,
        string $accountName = null,
        string $accountId = null,
        PlaidTokenType $plaidTokenType = null
    ): ApiResponse {
        $body = new LinkAccountMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $accountName,
            $plaidToken,
            $accountId,
            null,
            null,
            null,
            $plaidTokenType
        );
        $path = ApiEndpoints::LINK_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, LinkAccountResponse::class);
    }

    /**
     * Uses an MX processor token to link a bank account to a verified
     * entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string|null $accountName
     * @param string|null $accountId
     * @param string $provider
     * @param string $providerToken
     * @param string $providerTokenType
     * @return ApiResponse
     */
    public function linkAccountMx(
        string $userHandle,
        string $userPrivateKey,
        string $provider = "mx",
        string $providerToken,
        string $providerTokenType = "processor",
        string $accountName = null,
        string $accountId = null
    ): ApiResponse {
        $body = new LinkAccountMxMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $provider,
            $providerToken,
            $providerTokenType,
            $accountName,
            $accountId
        );
        $path = ApiEndpoints::LINK_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, LinkAccountResponse::class);
    }

    /**
     * Uses an account name to update said name from an entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $accountName
     * @param string $newAccountName
     * @param bool|true $isActive Optional
     * @return ApiResponse
     */
    public function updateAccount(
        string $userHandle,
        string $userPrivateKey,
        string $accountName,
        string $newAccountName,
        bool $isActive=true
    ): ApiResponse {
        $body = new UpdateAccountMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $accountName,
            $newAccountName,
            $isActive
        );
        
        $path = ApiEndpoints::UPDATE_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, UpdateAccountResponse::class);
    }

    /**
     * Uses a tabapay token to link a card to a verified entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $cardName
     * @param string $token
     * @param string|null $accountPostalCode
     * @param bool|false $skipVerification Optional
     * @return ApiResponse
     */
    public function linkCard(
        string $userHandle,
        string $userPrivateKey,
        string $cardName,
        string $token,
        string $accountPostalCode = null,
        string $provider,
        bool   $skipVerification = false
    ): ApiResponse {
        $body = new LinkCardMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $cardName,
            $token,
            $accountPostalCode,
            $provider,
            $skipVerification
        );
        $path = ApiEndpoints::LINK_CARD;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, LinkCardResponse::class);
    }

    /**
     * Gets a paginated list of "wallets"/blockchain addresses attached to a user handle.
     *
     * @param string $userHandle
     * @param SearchFilters $searchFilters
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     */
    public function getCards(
        string $userHandle,
        string $userPrivateKey
    ): ApiResponse {
        $body = new GetCardsMessage($userHandle, $this->configuration->getAppHandle());
        $path = ApiEndpoints::GET_CARDS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Uses a card name to delete a card from a verified entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $cardName
     * @return ApiResponse
     */
    public function deleteCard(
        string $userHandle,
        string $userPrivateKey,
        string $cardName,
        string $provider
    ): ApiResponse {
        $body = new DeleteCardMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $cardName,
            $provider
        );
        $path = ApiEndpoints::DELETE_CARD;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, DeleteCardResponse::class);
    }
    /**
     * Verify KYC status of an entity in other application.
     *
     * @param string $userHandle
     * @param string $queryAppHandle
     * @param string $queryUserHandle
     * @return ApiResponse
     */
     public function checkPartnerKYC(
        string $userHandle,
        string $queryAppHandle,
        string $queryUserHandle
    ): ApiResponse {
        $body = new CheckPartnerKYCMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $queryAppHandle,
            $queryUserHandle
        );
        $path = ApiEndpoints::CHECK_PARTNER_KYC;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Uses an account name to delete a bank account from an entity.
     *
     * @param string $userHandle
     * @param string $accountName
     * @return ApiResponse
     */
    public function deleteAccount(
        string $userHandle,
        string $accountName
    ): ApiResponse {
        $body = new DeleteAccountMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $accountName
        );
        $path = ApiEndpoints::DELETE_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, DeleteAccountResponse::class);
    }

    /**
     * Uses a provided Plaid public token to link a bank account to a verified 
     * entity.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $accountNumber
     * @param string routingNumber
     * @param string|null $accountName
     * @param string|null $accountType
     * @return ApiResponse
     */
    public function linkAccountDirect(
        string $userHandle,
        string $userPrivateKey,
        string $accountNumber,
        string $routingNumber,
        string $accountName = null,
        string $accountType = null
    ): ApiResponse {
        $body = new LinkAccountMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $accountName,
            null,
            null,
            $accountNumber,
            $routingNumber,
            $accountType
        );
        $path = ApiEndpoints::LINK_ACCOUNT;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, LinkAccountResponse::class);
    }

    /**
     * Gets basic bank account names linked to user handle.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws ClientException
     */
    public function getAccounts(string $userHandle, string $userPrivateKey): ApiResponse
    {
        $body = new GetAccountsMessage($userHandle, $this->configuration->getAppHandle());
        $path = ApiEndpoints::GET_ACCOUNTS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, 'array<' . Account::class . '>');
    }

    /**
     * Gets a list of institutions related to a linked account.
     *
     * @param SearchFilters|null $filters Optional
     * @return ApiResponse
     * @throws ClientException
     */
    public function getInstitutions(SearchFilters $filters = null): ApiResponse
    {
        $body = new GetInstitutionsMessage($this->configuration->getAppHandle(), $filters);
        $path = ApiEndpoints::GET_INSTITUTIONS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
       return $this->prepareResponse($response, GetInstitutionsResponse::class);
    }

    /**
     * Gets account balance.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $accontName
     * @return ApiResponse
     * @throws ClientException
     */
    public function getAccountBalance(string $userHandle, string $userPrivateKey, string $accontName): ApiResponse
    {
        $body = new GetAccountBalanceMessage($userHandle, $this->configuration->getAppHandle(), $accontName);
        $path = ApiEndpoints::GET_ACCOUNT_BALANCE;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, GetAccountBalanceResponse::class);
    }

    /**
     * Debits a specified account and issues tokens to the address belonging to
     * the requested handle.
     *
     * @param string $userHandle
     * @param float $amount Min Length 1, Max digits 35
     * @param string $accountName Max Length 40
     * @param string $userPrivateKey
     * @param string|null $descriptor Optional. Max Length 100
     * @param string|null $businessUuid Optional. UUID of a business with an approved ACH name. The format should be a UUID string.
     * @param \Silamoney\Client\Domain\AchType|null $processingType Optional. Choice Field
     * @param string $cardName Optional field
     * @param string $sourceId Optional field
     * @param string $destinationId Optional field
     * @return ApiResponse
     */
    public function issueSila(
        string $userHandle,
        float $amount,
        string $accountName = null,
        string $userPrivateKey,
        string $descriptor = null,
        string $businessUuid = null,
        AchType $processingType = null,
        string $cardName = null,
        string $sourceId = null,
        string $destinationId = null,
        string $transactionIdempotencyId = null
    ): ApiResponse 
    {
        $body = new BankAccountMessage(
            $userHandle,
            $accountName,
            $amount,
            $this->configuration->getAppHandle(),
            Message::ISSUE(),
            $descriptor,
            $businessUuid,
            $processingType,
            $cardName,
            $sourceId,
            $destinationId,
            $transactionIdempotencyId

        );
        $path = ApiEndpoints::ISSUE_SILA;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, OperationResponse::class);
    }

    /**
     * Starts a transfer of the requested amount of SILA to the requested destination handle.
     *
     * @param string $userHandle
     * @param string $destination
     * @param float $amount
     * @param string $userPrivateKey
     * @param string $destinationAddress
     * @param string $descriptor
     * @param string $businessUuid Optional field
     * @param string $sourceId Optional field
     * @param string $destinationId Optional field
     * @param string $transactionIdempotencyId Optional field
     * @return ApiResponse
     */
    public function transferSila(
        string $userHandle,
        string $destination,
        float $amount,
        string $userPrivateKey,
        string $destinationAddress = null,
        string $destinationWalletName = null,
        string $descriptor = null,
        string $businessUuid = null,
        string $sourceId = null,
        string $destinationId = null,
        string $transactionIdempotencyId = null
    ): ApiResponse 
    {
        $body = new TransferMessage(
            $userHandle,
            $destination,
            $amount,
            $this->configuration->getAppHandle(),
            $destinationAddress,
            $destinationWalletName,
            $descriptor,
            $businessUuid,
            $sourceId,
            $destinationId,
            $transactionIdempotencyId
        );
        $path = ApiEndpoints::TRANSFER_SILA;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, TransferResponse::class);
    }

    /**
     * Burns given amount of SILA at the handle's blockchain address and credits
     * their named bank account in the equivalent monetary amount.
     *
     * @param string $userHandle
     * @param float $amount Min Length 1, Max digits 35
     * @param string $accountName Max Length 40
     * @param string $userPrivateKey
     * @param string|null $descriptor Optional. Max Length 100
     * @param string|null $businessUuid Optional. UUID of a business with an approved ACH name. The format should be a UUID string.
     * @param \Silamoney\Client\Domain\AchType|null $processingType Optional. Choice Field
     * @param string|null $cardName Optional.
     * @param string|null $sourceId Optional.
     * @param string|null $destinationId Optional.
     * @return ApiResponse
     */
    public function redeemSila(
        string $userHandle,
        float $amount,
        string $accountName = null,
        string $userPrivateKey,
        string $descriptor = null,
        string $businessUuid = null,
        AchType $processingType = null,
        string $cardName = null,
        string $sourceId = null,
        string $destinationId = null,
        string $transactionIdempotencyId = null
        ): ApiResponse 
        {
        $body = new BankAccountMessage(
            $userHandle,
            $accountName,
            $amount,
            $this->configuration->getAppHandle(),
            Message::REDEEM(),
            $descriptor,
            $businessUuid,
            $processingType,
            $cardName,
            $sourceId,
            $destinationId,
            $transactionIdempotencyId
        );
        $path = ApiEndpoints::REDEEM_SILA;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, OperationResponse::class);
    }

    /**
     * Gets array of user handle's transactions with detailed status information.
     *
     * @param string $userHandle
     * @param \Silamoney\Client\Domain\SearchFilters $filters
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function getTransactions(string $userHandle = null, SearchFilters $filters): ApiResponse
    {
        $body = new GetTransactionsMessage($userHandle, $this->configuration->getAppHandle(), $filters);
        $path = ApiEndpoints::GET_TRANSACTIONS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, GetTransactionsResponse::class);
    }

    /**
     * Gets array of user handle's transaction statements with detailed status information.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param \Silamoney\Client\Domain\SearchFilters $filters
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function getStatementsData(string $userHandle = null, string $userPrivateKey, SearchFilters $filters): ApiResponse
    {
        $body = new GetStatementsMessage($userHandle, $this->configuration->getAppHandle(),Message::GET_STATEMENTS_DATA, $filters);
        $path = ApiEndpoints::GET_STATEMENTS_DATA;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, GetStatementsResponse::class);
    }

    /**
     * Gets array of user handle's transaction wallet statements with detailed status information.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param \Silamoney\Client\Domain\SearchFilters $filters
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function getWalletStatementData(string $userHandle = null, string $userPrivateKey, string $walletId, SearchFilters $filters): ApiResponse
    {
        $body = new GetStatementsMessage($userHandle, $this->configuration->getAppHandle(),Message::GET_WALLET_STATEMENT_DATA, $filters, $walletId);
        $path = ApiEndpoints::GET_WALLET_STATEMENT_DATA;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, GetStatementsResponse::class);
    }

    /**
     * Gets array of statements as per filter statements
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param \Silamoney\Client\Domain\SearchFilters $filters
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
    */
    public function statements(string $userHandle, string $userPrivateKey, SearchFilters $searchFilters): ApiResponse
    {
        $body = new StatementsMessage($userHandle, $userPrivateKey, $searchFilters);
        $path = ApiEndpoints::STATEMENTS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callGetAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * To resend statement request on email : statement/<statement_id>, resendStatements
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $statement_id is UUID of statement to be sent
     * @param string $email The email to request resend statement 
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
    */
    public function resendStatements( string $userHandle, string $userPrivateKey, string $statement_id, string $email ): ApiResponse 
    {
        $body = new ResendStatementsMessage($userHandle, $userPrivateKey, $email);
        $path = ApiEndpoints::STATEMENTS . '/'. $statement_id;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callPutAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Reverse a transaction using the transactionId
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $transactionId The transaction id to cancel
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function reverseTransactions(string $userHandle, string $userPrivateKey, string $transactionId): ApiResponse
    {
        $path = ApiEndpoints::REVERSE_TRANSACTION;
        $body = new ReverseTransactionMessage($this->configuration->getAppHandle(), $userHandle, $transactionId);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }
    
    /**
     * Gets Sila balance for a given blockchain address.
     *
     * @param string $address
     * @return ApiResponse
     * @throws \GuzzleHttp\Exception\ServerException
     */
    public function silaBalance(string $address): ApiResponse
    {
        $body = new SilaBalanceMessage($address);
        $path = ApiEndpoints::GET_SILA_BALANCE;
        $json = $this->serializer->serialize($body, 'json');
        $response = $this->configuration->getApiClient()->callAPI($path, $json, []);
        return $this->prepareResponse($response);
    }

    /**
     * Gets details about the user wallet used to generate the usersignature header..
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     */
    public function getWallet(string $userHandle, string $userPrivateKey): ApiResponse
    {
        $body = new GetWalletMessage($userHandle, $this->configuration->getAppHandle());
        $path = ApiEndpoints::GET_WALLET;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Adds another "wallet"/blockchain address to a user handle.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey An already registered wallet private key of the user
     * @param string $walletPrivateKey The new wallet's private key
     * @param SilaWallet $wallet The information of the new wallet (address, nickname, blockchain network)
     * @return ApiResponse 
     */
    public function addWallet(
        string $userHandle,
        string $userPrivateKey,
        string $walletPrivateKey,
        SilaWallet $wallet
    ): ApiResponse {
        $body = RegisterWalletMessage::fromPrivateKey(
            $userHandle,
            $this->configuration->getAppHandle(),
            $wallet,
            $walletPrivateKey
        );
        return $this->sendNewWallet($body, $userPrivateKey);
    }

    /**
     * Adds another "wallet"/blockchain address to a user handle.
     *
     * @param string $userHandle
     * @param SilaWallet $wallet
     * @param string $wallet_verification_signature
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     * @deprecated Since version 0.2.13-rc-4. Use addWallet instead for an easier implementation.
     */
    public function registerWallet(
        string $userHandle,
        SilaWallet $wallet,
        string $wallet_verification_signature,
        string $userPrivateKey
    ): ApiResponse {
            $body = new RegisterWalletMessage(
            $userHandle,
            $this->configuration->getAppHandle(),
            $wallet,
            $wallet_verification_signature
        );
        return $this->sendNewWallet($body, $userPrivateKey);
    }

    /**
     * Updates nickname and/or default status of a wallet.
     *
     * @param string $userHandle
     * @param string $nickname
     * @param boolean $status
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     */
    public function updateWallet(
        string $userHandle,
        string $nickname,
        bool $default,
        string $userPrivateKey,
        ?bool $statements_enabled
    ): ApiResponse {
        $body = new UpdateWalletMessage($userHandle, $this->configuration->getAppHandle(), $nickname, $default, $statements_enabled);
        $path       =   ApiEndpoints::UPDATE_WALLET;
        $json       =   $this->serializer->serialize($body, 'json');
        $headers    =   $this->makeHeaders($json, $userPrivateKey);
        $response   =   $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Deletes a user wallet.
     *
     * @param string $userHandle
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     */
    public function deleteWallet(string $userHandle, string $userPrivateKey): ApiResponse
    {
        $body = new DeleteWalletMessage($userHandle, $this->configuration->getAppHandle());
        $path = ApiEndpoints::DELETE_WALLET;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Gets a paginated list of "wallets"/blockchain addresses attached to a user handle.
     *
     * @param string $userHandle
     * @param SearchFilters $searchFilters
     * @param string $userPrivateKey
     * @return ApiResponse
     * @throws Exception
     */
    public function getWallets(
        string $userHandle,
        string $userPrivateKey,
        SearchFilters $searchFilters = null
    ): ApiResponse {
        $body = new GetWalletsMessage($userHandle, $this->configuration->getAppHandle(), $searchFilters);
        $path = ApiEndpoints::GET_WALLETS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Gets a list of valid business types that can be registered.
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getBusinessTypes(): ApiResponse
    {
        $path = ApiEndpoints::GET_BUSINESS_TYPES;
        return $this->makeHeaderBaseRequest($path);
    }

    /**
     * Retrieves the list of pre-defined business roles.
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getBusinessRoles(): ApiResponse
    {
        $path = ApiEndpoints::GET_BUSINESS_ROLES;
        return $this->makeHeaderBaseRequest($path);
    }

    /**
     * Gets a list of valid Naics categories that can be registered.
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getNaicsCategories(): ApiResponse
    {
        $path = ApiEndpoints::GET_NAICS_CATEGORIES;
        return $this->makeHeaderBaseRequest($path);
    }

    /**
     * Gets a particular entity registered in the app handle
     * @param string $userHandle The user handle to retrieve information from
     * @param string $userPrivateKey The user private key to sign the request
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getEntity(string $userHandle, string $userPrivateKey)
    {
        $path = ApiEndpoints::GET_ENTITY;
        $body = new HeaderBaseMessage($this->configuration->getAppHandle(), $userHandle);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Gets all the entities registered in the app handle
     * @param string|null $entityType Filters the results for 'individual' or 'business'
     * @param int|null $page Indicates the page to retrieve
     * @param int|null $perPage Indicates the number of entities per page to retrieve
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getEntities(
        string $entityType = null,
        int $page = null,
        int $perPage = null
    ) {
        $params = $this->pageParams($page, $perPage);
        $path = ApiEndpoints::GET_ENTITIES."{$params}";
        $body = new GetEntitiesMessage($this->configuration->getAppHandle(), $entityType);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Creates a new end-user managed by the app. This user can be verified, have bank accounts, and create transactions.
     * @param \Silamoney\Client\Domain\BusinessUser $business The new business information
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function registerBusiness(BusinessUser $business): ApiResponse
    {
        $path = ApiEndpoints::REGISTER;
        $body = new BusinessEntityMessage($this->configuration->getAppHandle(), $business);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Links an existing user to the indicated business with the role provided.
     * If the user is a beneficial owner you must provide the ownership stake.
     * If you don't provide the member handle, the user handle will be used to apply the requested role
     * @param string $businessHandle The business handle
     * @param string $businessPrivateKey The business private key for the request signature
     * @param string $userHandle The user handle. This can be a new user or an administrator if the member handle is provided
     * @param string $userPrivateKey The user private key for the request signature
     * @param string|null $role The member role. This is required unless the role uuid is set.
     * @param string|null $roleUuid The role uuid. This is required unless the role is set.
     * @param float|null $ownershipStake The onwership stake. This is required if the role or role uuid is a beneficial owner
     * @param string|null $memberHandle The member handle. This is optional, but if set the user handle must be an administrator of the business
     * @param string|null $details An optional descriptive text for the link operation
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function linkBusinessMember(
        string $businessHandle,
        string $businessPrivateKey,
        string $userHandle,
        string $userPrivateKey,
        string $role = null,
        string $roleUuid = null,
        float $ownershipStake = null,
        string $memberHandle = null,
        string $details = null
    ): ApiResponse {
        $path = ApiEndpoints::LINK_BUSINESS_MEMBER;
        $body = new LinkBusinessMemberMessage(
            $this->configuration->getAppHandle(),
            $businessHandle,
            $userHandle,
            $role,
            $roleUuid,
            $ownershipStake,
            $memberHandle,
            $details
        );
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeBusinessHeaders($json, $businessPrivateKey, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Unlinks the specified role from an existing user associated to the business handle
     * @param string $businessHandle The business handle
     * @param string $businessPrivateKey The business private key for the request signature
     * @param string $userHandle The user handle to be unlinked
     * @param string $userPrivateKey The user private key for the request signature
     * @param string|null $role The member role to unlink. This is required unless the role uuid is set.
     * @param string|null $roleUuid The role uuid to unlink. This is required unless the role is set.
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function unlinkBusinessMember(
        string $businessHandle,
        string $businessPrivateKey,
        string $userHandle,
        string $userPrivateKey,
        string $role = null,
        string $roleUuid = null
    ): ApiResponse {
        $path = ApiEndpoints::UNLINK_BUSINESS_MEMBER;
        $body = new UnlinkBusinessMemberMessage(
            $this->configuration->getAppHandle(),
            $businessHandle,
            $userHandle,
            $role,
            $roleUuid
        );
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeBusinessHeaders($json, $businessPrivateKey, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Certification process for the specified beneficial owner in the business
     * @param string $businessHandle The business handle
     * @param string $businessPrivateKey The business private key for the request signature
     * @param string $userHandle The user handle of an administrator
     * @param string $userPrivateKey The user private key for the request signature
     * @param string $memberHandle The beneficial owner handle to certify
     * @param string $certificationToken The certification token obtained from getEntity method for the specified beneficial owner handle
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function certifyBeneficialOwner(
        string $businessHandle,
        string $businessPrivateKey,
        string $userHandle,
        string $userPrivateKey,
        string $memberHandle,
        string $certificationToken
    ): ApiResponse {
        $path = ApiEndpoints::CERTIFY_BENEFICIAL_OWNER;
        $body = new CertifyBeneficialOwnerMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $businessHandle,
            $memberHandle,
            $certificationToken
        );
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeBusinessHeaders($json, $businessPrivateKey, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Certification process for the specified business
     * @param string $businessHandle The business handle to certify
     * @param string $businessPrivateKey The business private key for the request signature
     * @param string $userHandle The user handle of an administrator
     * @param string $userPrivateKey The user private key for the request signature
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function certifyBusiness(
        string $businessHandle,
        string $businessPrivateKey,
        string $userHandle,
        string $userPrivateKey
    ): ApiResponse {
        $path = ApiEndpoints::CERTIFY_BUSINESS;
        $body = new BaseBusinessMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $businessHandle
        );
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeBusinessHeaders($json, $businessPrivateKey, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * List the document types for KYC supporting documentation
     * @param int|null $page Indicates the page to retrieve
     * @param int|null $perPage Indicates the number of document types per page to retrieve
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getDocumentTypes(int $page = null, int $perPage = null): ApiResponse
    {
        $params = $this->pageParams($page, $perPage);
        $path = ApiEndpoints::DOCUMENT_TYPES."{$params}";
        $body = new BaseMessage($this->configuration->getAppHandle());
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Cancel a pending transaction under certain circumstances
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $transactionId The transaction id to cancel
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function cancelTransaction(string $userHandle, string $userPrivateKey, string $transactionId): ApiResponse
    {
        $path = ApiEndpoints::CANCEL_TRANSACTION;
        $body = new CancelTransactionMessage($this->configuration->getAppHandle(), $userHandle, $transactionId);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Add a new email to a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $email The new email
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function addEmail(string $userHandle, string $userPrivateKey, string $email): ApiResponse
    {
        $body = new EmailMessage($this->configuration->getAppHandle(), $userHandle, $email);
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::ADD(), RegistrationDataType::EMAIL(), $body);
    }

    /**
     * Update an existing email of a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $uuid
     * @param string $email The new email
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function updateEmail(string $userHandle, string $userPrivateKey, string $uuid, ?string $email = null): ApiResponse
    {
        $body = new EmailMessage($this->configuration->getAppHandle(), $userHandle, $email, $uuid);
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::UPDATE(), RegistrationDataType::EMAIL(), $body);
    }

    /**
     * Add a new phone number to a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $phone The new phone
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function addPhone(string $userHandle, string $userPrivateKey, string $phone): ApiResponse
    {
        $body = new PhoneMessage($this->configuration->getAppHandle(), $userHandle, $phone);
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::ADD(), RegistrationDataType::PHONE(), $body);
    }

    /**
     * Update an existing phone number of a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $uuid
     * @param string $phone The new phone
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function updatePhone(string $userHandle, string $userPrivateKey, string $uuid,  ?string $phone = null): ApiResponse
    {
        $body = new PhoneMessage($this->configuration->getAppHandle(), $userHandle, $phone, $uuid);
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::UPDATE(), RegistrationDataType::PHONE(), $body);
    }

    /**
     * Add a new identity to a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param \Silamoney\Client\Domain\IdentityAlias $identityAlias The identity type
     * @param string $identityValue The identity number
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function addIdentity(string $userHandle, string $userPrivateKey, IdentityAlias $identityAlias, string $identityValue): ApiResponse
    {
        $body = new IdentityMessage($this->configuration->getAppHandle(), $userHandle, $identityAlias, $identityValue);
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::ADD(), RegistrationDataType::IDENTITY(), $body);
    }

    /**
     * Update an existing identity of a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $uuid
     * @param \Silamoney\Client\Domain\IdentityAlias $identityAlias The identity type
     * @param string $identityValue The identity number
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function updateIdentity(
        string $userHandle,
        string $userPrivateKey,
        string $uuid,
        ?IdentityAlias $identityAlias = null,
        ?string $identityValue = null
    ): ApiResponse {
        $body = new IdentityMessage($this->configuration->getAppHandle(), $userHandle, $identityAlias, $identityValue, $uuid);
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::UPDATE(), RegistrationDataType::IDENTITY(), $body);
    }

    /**
     * Add a new address to a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $addressAlias This is a nickname that can be attached to the address object. While a required field, it can be left blank if desired.
     * @param string $streetAddress1 This is line 1 of a street address. Post office boxes are not accepted in this field.
     * @param string $city Name of the city where the person being verified is a current resident
     * @param string $state Name of state where verified person is a current resident.
     * @param \Silamoney\Client\Domain\Country $country Two-letter country code.
     * @param string $postalCode In the US, this can be the 5-digit ZIP code or ZIP+4 code
     * @param string $streetAddress2 This is line 2 of a street address (optional). This may include suite or apartment numbers
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function addAddress(
        string $userHandle,
        string $userPrivateKey,
        string $addressAlias,
        string $streetAddress1,
        string $city,
        string $state,
        Country $country,
        string $postalCode,
        string $streetAddress2 = null
    ): ApiResponse {
        $body = new AddressMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $addressAlias,
            $streetAddress1,
            $city,
            $state,
            $country,
            $postalCode,
            $streetAddress2
        );
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::ADD(), RegistrationDataType::ADDRESS(), $body);
    }

    /**
     * Update an existing address of a registered entity.
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string $uuid
     * @param string $addressAlias Optional. This is a nickname that can be attached to the address object. While a required field, it can be left blank if desired.
     * @param string $streetAddress1 Optional. This is line 1 of a street address. Post office boxes are not accepted in this field.
     * @param string $city Optional. Name of the city where the person being verified is a current resident
     * @param string $state Optional. Name of state where verified person is a current resident.
     * @param \Silamoney\Client\Domain\Country $country Opational. Two-letter country code.
     * @param string $postalCode Optional. In the US, this can be the 5-digit ZIP code or ZIP+4 code
     * @param string $streetAddress2 Optional. This is line 2 of a street address. This may include suite or apartment numbers
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function updateAddress(
        string $userHandle,
        string $userPrivateKey,
        string $uuid,
        ?string $addressAlias = null,
        ?string $streetAddress1 = null,
        ?string $city = null,
        ?string $state = null,
        ?Country $country = null,
        ?string $postalCode = null,
        ?string $streetAddress2 = null
    ): ApiResponse {
        $body = new AddressMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $addressAlias,
            $streetAddress1,
            $city,
            $state,
            $country,
            $postalCode,
            $streetAddress2,
            $uuid
        );
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::UPDATE(), RegistrationDataType::ADDRESS(), $body);
    }

    /**
     * Update a registered individual entity
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $entityName
     * @param string|null $birthdate
     * @param string|null $businessType
     * @param string|null $naicsCode
     * @param string|null $doingBusinessAs
     * @param string|null $businessWebsite
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function updateEntity(
        string $userHandle,
        string $userPrivateKey,
        string $firstName = null,
        string $lastName = null,
        string $entityName = null,
        DateTime $birthdate = null
    ): ApiResponse {
        $body = new EntityUpdateMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $firstName,
            $lastName,
            $entityName,
            $birthdate
        );
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::UPDATE(), RegistrationDataType::ENTITY(), $body);
    }

    /**
     * Update a registered business entity
     * @param string $userHandle The user handle
     * @param string $userPrivateKey The user's private key
     * @param string|null $entityName
     * @param string|null $birthdate
     * @param string|null $businessType
     * @param string|null $naicsCode
     * @param string|null $doingBusinessAs
     * @param string|null $businessWebsite
     * @param string|null $registrationState
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function updateBusinessEntity(
        string $userHandle,
        string $userPrivateKey,
        string $entityName = null,
        DateTime $birthdate = null,
        string $businessType = null,
        string $naicsCode = null,
        string $doingBusinessAs = null,
        string $businessWebsite = null,
        string $registrationState = null
    ): ApiResponse 
    {
        $body = new EntityUpdateMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            null,
            null,
            $entityName,
            $birthdate,
            $businessType,
            $naicsCode,
            $doingBusinessAs,
            $businessWebsite,
            $registrationState
        );
        return $this->modifyRegistrationData($userPrivateKey, RegistrationDataOperation::UPDATE(), RegistrationDataType::ENTITY(), $body);
    }

    /**
     * Delete an existing email, phone number, street address, or identity.
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param \Silamoney\Client\Domain\RegistrationDataType $dataType Indicates which type of registration data to delete
     * @param string $uuid The registration data uuid
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function deleteRegistrationData(string $userHandle, string $userPrivateKey, RegistrationDataType $dataType, string $uuid): ApiResponse
    {
        $path = ApiEndpoints::DELETE."/{$dataType}";
        $body = new DeleteRegistrationMessage($this->configuration->getAppHandle(), $userHandle, $uuid);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Upload supporting documentation for KYC
     *  This function Should be deprecated and use uploadDocuments instead 
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $filePath
     * @param string $filename
     * @param string $mimeType
     * @param string $documentType
     * @param string $identityType
     * @param string|null $name
     * @param string|null $description
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function uploadDocument(
        string $userHandle,
        string $userPrivateKey,
        string $filePath,
        string $filename,
        string $mimeType,
        string $documentType,
        string $identityType = null, //depricated and should be removed
        string $name = null,
        string $description = null
    ): ApiResponse {
        $path = ApiEndpoints::DOCUMENTS;
        $file = fopen($filePath, 'rb');
        $contents = fread($file, filesize($filePath));
        fclose($file);
        $hash = hash('sha256', $contents);
        $body = new DocumentMessage(
            $this->configuration->getAppHandle(),
            $userHandle,
            $filename,
            $hash,
            $mimeType,
            $documentType,
            $name,
            $description
        );
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $data = [
            ['name' => 'file', 'contents' => fopen($filePath, 'rb')],
            ['name' => 'data', 'contents' => $json]
        ];
        $response = $this->configuration->getApiClient()->callFileApi(
            $path,
            $data,
            $headers
        );
        
        return $this->prepareResponse($response);
    }

    /**
     * Upload supporting documentation for KYC
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param array $documents
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function uploadDocuments(
        string $userHandle,
        string $userPrivateKey,
        $documents // array of one or more documents
    ): ApiResponse {
        $path = ApiEndpoints::DOCUMENTS;
        if(gettype($documents) === "array") {
            $files = [];
            $files_metadata = [];
            $data = [];
            $counter = 0;
            foreach ($documents as $key => $document) {
                $counter++;
                $filePath = $document["filePath"];
                $filename = $document["filename"];
                $mimeType = $document["mimeType"];
                $documentType = $document["documentType"];
                $name = !empty($document["name"])?$document["name"]:null;
                $description = !empty($document["description"])?$document["description"]:null;
                
                $file = fopen($filePath, 'rb');
                $contents = fread($file, filesize($filePath));
                fclose($file);
                $hash = hash('sha256', $contents);
                
                $files["file_".$counter] = fopen($filePath, 'rb');
                
                $body = new DocumentWithoutHeaderMessage(
                    $this->configuration->getAppHandle(),
                    $userHandle,
                    $filename,
                    $hash,
                    $mimeType,
                    $documentType,
                    ($name),
                    $description
                );
                $json = $this->serializer->serialize($body, 'json');
                $json = $body;
                $files_metadata["file_".$counter] = $json;
                
                $data[] = ["name" => "file_".($counter), "contents" => fopen($filePath, 'rb')];
            }

            $fullBody = new DocumentListMessage(
                $this->configuration->getAppHandle(),
                $userHandle,
                $files_metadata
            );
            $fullJson = $this->serializer->serialize($fullBody, 'json');
            $headers = $this->makeHeaders($fullJson, $userPrivateKey);
            $data[] = ["name" => "data", "contents" => $fullJson];
            
            $response = $this->configuration->getApiClient()->callFileApi(
                $path,
                $data,
                $headers
            );
        } else {
            throw new InvalidArgumentException('The documents variable must be an array');
        }
        return $this->prepareResponse($response);
    }

    /**
     * List previously uploaded supporting documentation for KYC
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param int|null $page Page number to retrieve. Default: 1
     * @param int|null $perPage Number of items per page. Default: 20, max: 100
     * @param string|null $sort Sort returned items (usually by creation date). Allowed values: asc (default), desc
     * @param DateTime|null $startDate Only return documents created on or after this date.
     * @param DateTime|null $endDate Only return documents created before or on this date.
     * @param array<string>|null $docTypes You can get this values from getDocumentTypes.
     * @param string|null $search Only return documents whose name or filename contains the search value. Partial matches allowed, no wildcards.
     * @param string|null $sortBy One of: name or date
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function listDocuments(
        string $userHandle,
        string $userPrivateKey,
        int $page = null,
        int $perPage = null,
        string $sort = null,
        DateTime $startDate = null,
        DateTime $endDate = null,
        array $docTypes = null,
        string $search = null,
        string $sortBy = null
    ): ApiResponse {
        $params = $this->pageParams($page, $perPage, $sort);
        $path = ApiEndpoints::LIST_DOCUMENTS."{$params}";
        $body = new ListDocumentsMessage($this->configuration->getAppHandle(), $userHandle, $startDate, $endDate, $docTypes, $search, $sortBy);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Retrieve a previously uploaded supporting documentation for KYC
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $uuid The document id
     * @return \Silamoney\Client\Api\ApiResponse
     */
    public function getDocument(string $userHandle, string $userPrivateKey, string $uuid): ApiResponse
    {
        $path = ApiEndpoints::GET_DOCUMENT;
        $body = new GetDocumentMessage($this->configuration->getAppHandle(), $userHandle, $uuid);
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareFileResponse($response);
    }

    /**
     * Gets the configuration api client
     * @return \Silamoney\Client\Api\ApiClient
     */
    public function getApiClient(): ApiClient
    {
        return $this->configuration->getApiClient();
    }

    /**
     * Create a new SilaWallet
     * @param string|null $privateKey
     * @param string|null $address
     * @param string|null $blockchainNetwork
     * @param string|null $nickname
     * @param bool|null $default
     * @return SilaWallet
     */
    public function generateWallet($privateKey = null, $address = null, $blockchainNetwork = null, $nickname = null, $default = null): SilaWallet
    {
        return new SilaWallet($privateKey, $address, $blockchainNetwork, $nickname, $default);
    }

    /**
     * Gets the configuration api client
     * @return \Silamoney\Client\Api\ApiClient
     */
    public function getBalanceClient(): ApiClient
    {
        return $this->configuration->getBalanceClient();
    }

    private function sendNewWallet(RegisterWalletMessage $body, string $userPrivateKey): ApiResponse
    {
        $path       =   ApiEndpoints::REGISTER_WALLET;
        $json       =   $this->serializer->serialize($body, 'json');
        $headers    =   $this->makeHeaders($json, $userPrivateKey);
        $response   =   $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * @param string $userPrivateKey
     * @param \Silamoney\Client\Domain\EmailMessage|\Silamoney\Client\Domain\PhoneMessage|\Silamoney\Client\Domain\IdentityMessage|\Silamoney\Client\Domain\AddressMessage|\Silamoney\Client\Domain\EntityUpdateMessage $body
     * @return \Silamoney\Client\Api\ApiResponse
     */
    private function modifyRegistrationData(
        string $userPrivateKey,
        RegistrationDataOperation $operation,
        RegistrationDataType $dataType,
        $body
    ): ApiResponse
    {
        switch (get_class($body)) {
            case EmailMessage::class:
            case PhoneMessage::class:
            case IdentityMessage::class:
            case AddressMessage::class:
            case EntityUpdateMessage::class:
                break;
            default:
                throw new InvalidArgumentException('addRegistrationData function only accepts: '
                    . EmailMessage::class . ', ' . ', ' . PhoneMessage::class . ', ' . IdentityMessage::class
                    . ', ' . AddressMessage::class . ', ' . EntityUpdateMessage::class . '. Input was: ' . get_class($body));
        }
        $path = "/{$operation}/{$dataType}";
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * Gets array of user handle's webhooks .
     *
     * @param string|null $userHandle
     * @param \Silamoney\Client\Domain\SearchFilters $filters
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function getWebhooks(string $userHandle = null, SearchFilters $filters): ApiResponse
    {
        $body = new GetWebhooksMessage($this->configuration->getAppHandle(), $userHandle, $filters);
        $path = ApiEndpoints::GET_WEBHOOKS;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response, GetWebhooksResponse::class);
    }

    /**
     * Gets a user handle's webhook using retry_webhook .
     *
     * @param string $eventUuid
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function retryWebhook(string $eventUuid): ApiResponse
    {
        $body = new RetryWebhookMessage($this->configuration->getAppHandle(), $eventUuid);
        $path = ApiEndpoints::RETRY_WEBHOOK;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callApi($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * @param int|null $page
     * @param int|null $perPage
     * @param string|null $sort
     * @return string
     */
    private function pageParams(int $page = null, int $perPage = null, string $sort = null): string
    {
        $params = '';
        if ($page != null) {
            $params = "?page={$page}";
        }
        if ($perPage != null) {
            if ($params != '') {
                $params = "{$params}&per_page={$perPage}";
            } else {
                $params = "?per_page={$perPage}";
            }
        }
        if ($sort != null) {
            if ($params != '') {
                $params = "{$params}&sort={$sort}";
            } else {
                $params = "?sort={$sort}";
            }
        }
        return $params;
    }

    /**
     * Makes a call to the specified path using only the HeaderBaseMessage as payload
     * @param string $path
     * @return \Silamoney\Client\Api\ApiResponse
     */
    private function makeHeaderBaseRequest(string $path): ApiResponse
    {
        $body = new HeaderBaseMessage($this->configuration->getAppHandle());
        $json = $this->serializer->serialize($body, 'json');
        $headers = [
            self::AUTH_SIGNATURE => EcdsaUtil::sign($json, $this->configuration->getPrivateKey())
        ];
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response);
    }

    /**
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $cardNumber
     * @param string $expiryMonth
     * @param string $expiryYear
     * @param string $ckoPublicKey
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function createCKOTestingToken(string $userHandle, string $userPrivateKey, $cardNumber, $expiryMonth, $expiryYear, $ckoPublicKey): ApiResponse
    {
        $body = new CreateCKOTestingTokenMessage($userHandle, $this->configuration->getAppHandle(), $cardNumber, $expiryMonth, $expiryYear, $ckoPublicKey);
        $path = ApiEndpoints::CREATE_CKO_TESTING_TOKEN;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response, CreateCKOTestingTokenResponse::class);
    }

    /**
     * @param string $userHandle
     * @param string $userPrivateKey
     * @param string $transaction_id
     * @return ApiResponse
     * @throws ClientException
     * @throws Exception
     */
    public function refundDebitCard(string $userHandle, string $userPrivateKey, $transaction_id): ApiResponse
    {
        $body = new RefundDebitCardMessage($userHandle, $this->configuration->getAppHandle(), $transaction_id);
        $path = ApiEndpoints::REFUND_DEBIT_CARD;
        $json = $this->serializer->serialize($body, 'json');
        $headers = $this->makeHeaders($json, $userPrivateKey);
        $response = $this->configuration->getApiClient()->callAPI($path, $json, $headers);
        return $this->prepareResponse($response, RefundDebitCardResponse::class);
    }

    /**
     * Makes the signing headers for request that uses auth_signature, user_signature and business_signature
     * @param string $json The json string body to sign
     * @param string $businessKey The business private key for the business_signature
     * @param string $userKey The user private key for the user_signature
     * @return array An associative array with the signatures
     */
    private function makeBusinessHeaders(string $json, string $businessKey, string $userKey): array
    {
        return $this->makeHeaders($json, $userKey, $businessKey);
    }

    /**
     * Makes the signing headers for request that uses auth_signature and/or user_signature and/or business_signature
     * @param string $json The json string body to sign
     * @param string|null $userKey The user private key for the user_signature
     * @param string|null $businessKey The business private key for the business_signature
     * @return array An associative array with the signatures
     */
    private function makeHeaders(string $json, string $userKey = null, string $businessKey = null): array
    {
        $headers = [];
        $headers[self::AUTH_SIGNATURE] = EcdsaUtil::sign($json, $this->configuration->getPrivateKey());
        if(!empty($userKey)) $headers[self::USER_SIGNATURE] = EcdsaUtil::sign($json, $userKey);
        if(!empty($businessKey)) $headers[self::BUSINESS_SIGNATURE] = EcdsaUtil::sign($json, $businessKey);

        return $headers;
    }

    /**
     * Makes the response object of multiple requests in the SDK
     * @param \GuzzleHttp\Psr7\Response The response from the request
     * @param string $className Optional. The object class name to deserialize the response to
     * @return \Silamoney\Client\Api\ApiResponse
     */
    private function prepareResponse(Response $response, string $className = ''): ApiResponse
    {
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();
        $body = null;
        if ($className == SilaBalanceResponse::class) {
            $contents = json_encode(json_decode($contents));
        }

        if ($statusCode == 200 && $className != '') {
            $body = $this->serializer->deserialize($contents, $className, 'json');
        } else {
            $body = json_decode($contents);
        }
        return new ApiResponse($statusCode, headers: $response->getHeaders(), data: $body);
    }

    private function prepareFileResponse(Response $response)
    {
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();
        if ($statusCode == 200) {
            return new ApiResponse($statusCode, $response->getHeaders(), $contents);
        } else {
            return new ApiResponse($statusCode, $response->getHeaders(), json_decode($contents));
        }
    }

    /**
     * Makes the response object for some requests in the SDK that return a BaseResponse object message
     * @param \GuzzleHttp\Psr7\Response The response from the request
     * @return \Silamoney\Client\Api\ApiResponse
     */
    private function prepareBaseResponse(Response $response): ApiResponse
    {
        return $this->prepareResponse($response, BaseResponse::class);
    }
}
