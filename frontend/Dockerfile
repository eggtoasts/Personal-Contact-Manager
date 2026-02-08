# Use nginx to serve static files
FROM nginx:alpine

# Remove default nginx website
RUN rm -rf /usr/share/nginx/html/*

# Copy all frontend files to nginx html directory
COPY . /usr/share/nginx/html/

# Create nginx configuration for Railway
RUN echo 'server {' > /etc/nginx/conf.d/default.conf && \
    echo '    listen 80;' >> /etc/nginx/conf.d/default.conf && \
    echo '    root /usr/share/nginx/html;' >> /etc/nginx/conf.d/default.conf && \
    echo '    index index.html;' >> /etc/nginx/conf.d/default.conf && \
    echo '' >> /etc/nginx/conf.d/default.conf && \
    echo '    location / {' >> /etc/nginx/conf.d/default.conf && \
    echo '        try_files $uri $uri/ /index.html;' >> /etc/nginx/conf.d/default.conf && \
    echo '    }' >> /etc/nginx/conf.d/default.conf && \
    echo '' >> /etc/nginx/conf.d/default.conf && \
    echo '    # CORS headers for API calls' >> /etc/nginx/conf.d/default.conf && \
    echo '    add_header Access-Control-Allow-Origin "*" always;' >> /etc/nginx/conf.d/default.conf && \
    echo '    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;' >> /etc/nginx/conf.d/default.conf && \
    echo '    add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;' >> /etc/nginx/conf.d/default.conf && \
    echo '}' >> /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
