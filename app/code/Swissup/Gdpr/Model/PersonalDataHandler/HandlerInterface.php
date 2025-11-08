<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;

interface HandlerInterface
{
    /**
     * Check if deletion request can be processed.
     *  - This method should throw an exception if there is some major
     *    incompleted operation is made by client (eg. pending order)
     *
     *  - DO NOT THROW ANY EXCEPTIONS in case of minor issues, as it will
     *    completely stop request processing by all modules.
     *
     * @return void
     * @throws \Exception
     */
    public function beforeDelete(ClientRequest $request);

    /**
     * Delete personal data.
     *  - Should physically remove related data.
     *  - Unlike to `beforeDelete` method, exceptions will not interrupt
     *    the process and will not affect Request status.
     *  - Exceptions will be added into request processing report.
     *
     * @return void
     * @throws \Exception
     */
    public function delete(ClientRequest $request);

    /**
     * Anonymize personal data.
     *  - Replace personal data with dummy placeholder.
     *  - Exceptions will be added into request processing report.
     *
     * @return void
     * @throws \Exception
     */
    public function anonymize(ClientRequest $request);

    /**
     * @todo: Export personal data
     *
     * @return array
     *         Example:
     *         [
     *             'customer' => [
     *                 '3rd-party' => 'value' // this data will be merged with
     *                                        // built-in handlers and written
     *                                        // into customer.csv file
     *             ],
     *             '3rd-party-file' => [
     *                 'key' => 'value'  // this data will be written into
     *                                   // 3rd-party-file.csv file, or into
     *                                   // separate section of single file (Human readable pdf file)
     *             ]
     *         ]
     * @throws \Exception
     */
    public function export(ClientRequest $request);
}
