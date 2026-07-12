FROM nginx:alpine

# نحتاج envsubst عشان يحقن المتغيرات في الملفات
RUN apk add --no-cache gettext

# نسخ ملفات الموقع
COPY . /usr/share/nginx/html

# ملف إعدادات nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# سكريبت التشغيل — يحقن المتغيرات المطلوبة فقط (ما يمسّ باقي $ بالكود)
RUN echo '#!/bin/sh' > /docker-entrypoint.sh \
  && echo 'envsubst "$CHAT_PASSWORD $CRISP_WEBSITE_ID" < /usr/share/nginx/html/chat.html > /usr/share/nginx/html/chat.html.tmp' >> /docker-entrypoint.sh \
  && echo 'mv /usr/share/nginx/html/chat.html.tmp /usr/share/nginx/html/chat.html' >> /docker-entrypoint.sh \
  && echo 'nginx -g "daemon off;"' >> /docker-entrypoint.sh \
  && chmod +x /docker-entrypoint.sh

CMD ["/docker-entrypoint.sh"]
