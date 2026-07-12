FROM nginx:alpine

# نسخ ملفات الموقع
COPY . /usr/share/nginx/html

# إنشاء سكريبت التشغيل (طريقة تقليدية مضمونة)
RUN echo '#!/bin/sh' > /docker-entrypoint.sh \
  && echo '' >> /docker-entrypoint.sh \
  && echo '# حقن كلمة السر من متغير البيئة' >> /docker-entrypoint.sh \
  && echo "sed -i \"s/__CHAT_PASSWORD__/\${CHAT_PASSWORD:-ejaza123}/g\" /usr/share/nginx/html/chat.html" >> /docker-entrypoint.sh \
  && echo '# حقن Crisp Website ID' >> /docker-entrypoint.sh \
  && echo "sed -i \"s/__CRISP_WEBSITE_ID__/\${CRISP_WEBSITE_ID:-YOUR_CRISP_WEBSITE_ID}/g\" /usr/share/nginx/html/chat.html" >> /docker-entrypoint.sh \
  && echo '' >> /docker-entrypoint.sh \
  && echo 'exec nginx -g "daemon off;"' >> /docker-entrypoint.sh \
  && chmod +x /docker-entrypoint.sh

EXPOSE 80
CMD ["/docker-entrypoint.sh"]
