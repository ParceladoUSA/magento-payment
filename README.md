# ParceladoUSA para Adobe Commerce / Magento

Módulo que permite a integração com a API de pagamento do ParceladoUSA.

## Configurações
Para o correto funcionamento do módulo, é necessário que seja selecionado se a API será utilizada em modo production ou sandbox, bem como a inserção da Merchant ID e Merchant Key, geradas no site do Parcelado API.

![image](https://user-images.githubusercontent.com/41929217/186962922-df9868c6-24ca-4407-9fc2-e15a2c350584.png)


## Endpoints
- Webhook para ser inserido no site do Parcelado API:<br>
  - Method: `POST`;
  - URL: `https://seusite.com/rest/V1/parcelado/payment/update`;
