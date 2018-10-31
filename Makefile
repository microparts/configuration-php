IMAGE = rg.teamc.io/teamc.io/microservice/configuration/php-pkg
VERSION = latest

image:
	docker build -t $(IMAGE):$(VERSION) -t $(IMAGE):latest .

push:
	docker push $(IMAGE):$(VERSION)
	docker push $(IMAGE):latest
