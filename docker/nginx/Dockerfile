FROM nginx:1.28-alpine

RUN apk add --no-cache wget

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD wget --no-verbose --tries=1 --spider http://127.0.0.1/health || exit 1
