FROM nginx:stable-alpine

ARG UID=1000
ARG GID=1000

RUN addgroup --gid "${GID}" --system app \
    && adduser --uid "${UID}" --ingroup app --system --disabled-password app \
    && sed -i 's/user  nginx;/user  app;/' /etc/nginx/nginx.conf

COPY default.conf /etc/nginx/conf.d/default.conf
