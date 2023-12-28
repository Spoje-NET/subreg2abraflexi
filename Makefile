buildimage:
	docker build -t vitexsoftware/subreg2abraflexi:latest .

buildx:
	docker buildx build . --push --platform linux/arm/v7,linux/arm64/v8,linux/amd64 --tag vitexsoftware/subreg2abraflexi:latest

drun:
	docker run --env-file .env vitexsoftware/subreg2abraflexi:latest
