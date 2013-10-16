<?php

class Mandrill_Error extends Exception {}
class Mandrill_HttpError extends Mandrill_Error {}

/**
 * The parameters passed to the API call are invalid or not provided when required
 */
class Mandrill_ValidationError extends Mandrill_Error {}

/**
 * The provided API key is not a valid Mandrill API key
 */
class Mandrill_Invalid_Key extends Mandrill_Error {}

/**
 * The requested feature requires payment.
 */
class Mandrill_PaymentRequired extends Mandrill_Error {}

/**
 * The provided subaccount id does not exist.
 */
class Mandrill_Unknown_Subaccount extends Mandrill_Error {}

/**
 * The requested template does not exist
 */
class Mandrill_Unknown_Template extends Mandrill_Error {}

/**
 * The subsystem providing this API call is down for maintenance
 */
class Mandrill_ServiceUnavailable extends Mandrill_Error {}

/**
 * The provided message id does not exist.
 */
class Mandrill_Unknown_Message extends Mandrill_Error {}

/**
 * The requested tag does not exist or contains invalid characters
 */
class Mandrill_Invalid_Tag_Name extends Mandrill_Error {}

/**
 * The requested email is not in the rejection list
 */
class Mandrill_Invalid_Reject extends Mandrill_Error {}

/**
 * The requested sender does not exist
 */
class Mandrill_Unknown_Sender extends Mandrill_Error {}

/**
 * The requested URL has not been seen in a tracked link
 */
class Mandrill_Unknown_Url extends Mandrill_Error {}

/**
 * The given template name already exists or contains invalid characters
 */
class Mandrill_Invalid_Template extends Mandrill_Error {}

/**
 * The requested webhook does not exist
 */
class Mandrill_Unknown_Webhook extends Mandrill_Error {}

/**
 * The requested inbound domain does not exist
 */
class Mandrill_Unknown_InboundDomain extends Mandrill_Error {}

/**
 * The requested export job does not exist
 */
class Mandrill_Unknown_Export extends Mandrill_Error {}

/**
 * A dedicated IP cannot be provisioned while another request is pending.
 */
class Mandrill_IP_ProvisionLimit extends Mandrill_Error {}

/**
 * The provided dedicated IP pool does not exist.
 */
class Mandrill_Unknown_Pool extends Mandrill_Error {}

/**
 * The provided dedicated IP does not exist.
 */
class Mandrill_Unknown_IP extends Mandrill_Error {}

/**
 * You cannot remove the last IP from your default IP pool.
 */
class Mandrill_Invalid_EmptyDefaultPool extends Mandrill_Error {}

/**
 * The default pool cannot be deleted.
 */
class Mandrill_Invalid_DeleteDefaultPool extends Mandrill_Error {}

/**
 * Non-empty pools cannot be deleted.
 */
class Mandrill_Invalid_DeleteNonEmptyPool extends Mandrill_Error {}


