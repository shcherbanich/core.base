<?php

namespace shcherbanich\core\helpers\microService;

/**
 * Оболочка для работы с микросервисом
 */
interface ServiceInterface {

    /**
     *
     * @return void
     */
    public function resetRequestHandlers();

    /**
     * Добавить заголовок
     *
     * @param string $key
     *
     * @param callable $handler обработчик ( запрос передается по ссылке )
     *
     * @return void
     */
    public function addRequestHandler($key, callable $handler = null);

    /**
     * Отправить запрос к микросервису
     *
     * @param RequestInterface $request
     *
     * @param array $options дополнительные параметры
     *
     * @return ResponseInterface $response
     */
    public function sendRequest(RequestInterface $request, array $options = []);
}
