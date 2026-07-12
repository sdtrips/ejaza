FROM nginx:alpine
COPY . /usr/share/nginx/html

# استخدم | كمحدد sed (بدل /) عشان ما يتعارض مع env vars اللي فيها /
CMD sed -i "s|__CHAT_PASSWORD__|${CHAT_PASSWORD:-ejaza123}|g" /usr/share/nginx/html/ch.html \
  && sed -i "s|__CRISP_WEBSITE_ID__|${CRISP_WEBSITE_ID:-YOUR_ID}|g" /usr/share/nginx/html/ch.html \
  && sed -i "s|__POSTHOG_API_KEY__|${POSTHOG_API_KEY:-}|g" /usr/share/nginx/html/ch.html \
  && sed -i "s|__POSTHOG_HOST__|${POSTHOG_HOST:-https://us.i.posthog.com}|g" /usr/share/nginx/html/ch.html \
  && nginx -g "daemon off;"
