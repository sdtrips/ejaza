FROM nginx:alpine

RUN apk add --no-cache gettext

# نسخ الملفات
COPY . /usr/share/nginx/html

# سكريبت دخول — يحقن متغيرات البيئة في chat.html ثم يشغل nginx
COPY <<"EOF" /docker-entrypoint.sh
#!/bin/sh
# حقن المتغيرات — يستبدل $CHAT_PASSWORD و $CRISP_WEBSITE_ID فقط
envsubst '$CHAT_PASSWORD $CRISP_WEBSITE_ID' < /usr/share/nginx/html/chat.html > /usr/share/nginx/html/chat.html.tmp
mv /usr/share/nginx/html/chat.html.tmp /usr/share/nginx/html/chat.html

# تشغيل nginx بالواجهة (daemon off)
nginx -g 'daemon off;'
EOF

RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

CMD ["/docker-entrypoint.sh"]
