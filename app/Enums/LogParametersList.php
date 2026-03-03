<?php

namespace App\Enums;

enum LogParametersList: string
{
    case FEATURE = 'feature';
    case SUB_FEATURE = 'sub_feature';
    case EXTRA = 'extra';
        // USER
    case USER_ID = 'user.id';

        // ERROR
    case ERROR_MESSAGE = 'error.message';
    case ERROR_TRACE = 'error.trace';
    case STATUS_CODE = 'status.code';

        // CACHE
    case CACHE_KEY = 'cache.key';
    case CACHE_RESOURCE_TYPE = 'cache.resource_type';

        // STORE
    case STORE_ID = 'store.id';

        // GENERAL
    case ERROR = 'error';
    case MESSAGE = 'message';
    case ORDER_ID = 'order.id';
    case PURCHASE_ID = 'purchase.id';
    case PRODUCT_ID = 'product.id';

        // TYPE

    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case EXPORT = 'export';
    case IMPORT = 'import';
    case VIEW = 'view';
    case SEARCH = 'search';
    case FILTER = 'filter';
    case SORT = 'sort';
    case PAGINATE = 'paginate';
    case VALIDATE = 'validate';


    case RETRIEVE = 'retrieve';
    case LIST = 'list';
    case AUTH = 'auth';
    case REQUEST = 'request';
}
