<?php

class Epusdt
{
    /**
     * The epusdt API endpoint.
     *
     * @var string
     */
    protected string $endpoint;

    /**
     * The epusdt token.
     *
     * @var string
     */
    protected string $token;

    /**
     * Create epusdt instance.
     *
     * @param   string  $token
     *
     * @return  void
     */
    public function __construct(string $endpoint, string $token)
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->token = $token;
    }

    public function curl_post($url, $para = '', $header = array('content-type: application/json'))
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //SSL证书认证false
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //严格认证false
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置HTTPHEADER
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para); // post传输数据
        $res = curl_exec($curl);

        $err = curl_error($curl);
        if ($err) {
            throw new Exception($err);
        }

        curl_close($curl);

        return $res;
    }

    /**
     * Generate the signature by given parameters.
     *
     * @param   array  $parameters
     *
     * @return  string
     */
    protected function signature(array $parameters = []): string
    {
        $parameters = array_filter($parameters, function ($key) {
            return !($key == 'signature');
        }, ARRAY_FILTER_USE_KEY);
        $parameters = array_filter($parameters);

        ksort($parameters);
        reset($parameters);

        $string  = "";
        foreach ($parameters as $key => $val) {
            $string .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $string = substr($string, 0, -1);

        $sign = md5(urldecode($string) . $this->token); //密码追加进入开始MD5签名

        return $sign;
    }

    /**
     * Verify the signature.
     *
     * @param   string  $signature
     * @param   array  $parameters
     *
     * @return  bool
     */
    public function verifySignature(string $signature, array $parameters = []): bool
    {
        return $signature === $this->signature($parameters);
    }

    /**
     * Generate the payment link.
     *
     * @param   array $parameters
     *
     * @return  string
     */
    public function redirectURL(array $parameters = [])
    {
        try {
            $parameters['signature'] = $this->signature($parameters);

            $res = $this->curl_post(
                $this->endpoint . '/api/v1/order/create-transaction',
                json_encode(
                    $parameters,
                    JSON_UNESCAPED_SLASHES
                )
            );

            $res = json_decode($res, true);

            if ($res["status_code"] != 200) {
                throw new Exception($res["message"]);
            }

            return $res['data']['payment_url'];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
