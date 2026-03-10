# syntax=docker/dockerfile:1.7

ARG MYSQL_BASE_IMAGE=mysql:8.4.8
ARG GOLANG_IMAGE=golang:1.25.7-bookworm

FROM ${GOLANG_IMAGE} AS gosu-builder
ARG GOSU_REF=1.19
WORKDIR /src
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates git \
    && rm -rf /var/lib/apt/lists/*
RUN git clone https://github.com/tianon/gosu.git . \
    && git checkout "${GOSU_REF}"
RUN CGO_ENABLED=0 go build -trimpath -ldflags="-s -w" -o /out/gosu .

FROM ${MYSQL_BASE_IMAGE}
USER root
RUN microdnf remove -y mysql-shell \
    && microdnf clean all \
    && rm -rf /var/cache/dnf
COPY --from=gosu-builder /out/gosu /usr/local/bin/gosu
RUN chmod 0755 /usr/local/bin/gosu \
    && gosu --version
