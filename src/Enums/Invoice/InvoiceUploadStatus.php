<?php

namespace Agriweather\EzPayInvoice\Enums\Invoice;

enum InvoiceUploadStatus: string
{
    /** 未上傳 */
    case NOT_UPLOADED = 0;
    /** 已上傳成功 */
    case UPLOADED = 1;
    /** 上傳中 */
    case UPLOADING = 2;
    /** 上傳失敗 */
    case UPLOAD_FAILED = 3;
    /** 上傳逾時 */
    case UPLOAD_TIMEOUT = 4;
}
