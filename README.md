# xcom.peacefulcraft.net
A RESTful JSON API for interacting with our statistics backends and cross-server-communications platform XCOM.

# Documentation
- (Internal) [`/party` route documentation](https://documenter.getpostman.com/view/15756566/TzRUA714)

# Development
1. `docker compose up`
2. `php scripts/director.php`
3. `database migrate`
4. Access:\
4.1 [API: http://127.0.0.1:8081](http://127.0.0.1:8081)\
4.2 [PMA: http://127.0.0.1:8082](http://127.0.0.1:8082)\
4.3 [RabbitMQ Management: http://127.0.0.1:8083](http://127.0.0.1:808)