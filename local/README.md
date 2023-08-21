# Normal usage

### *See the [setup-notes](https://github.com/UN-OCHA/local-reverse-proxy/blob/main/setup-notes.md) for first-time set-up.*

## To start
`docker compose -f local/docker-compose.yml up -d`
## To enter the container
`docker exec -it odsg-local-site sh`
## To stop
`docker compose -f local/docker-compose.yml down`

## Notes
Composer should be run from inside the container.
