# Use nginx to serve static files
FROM nginx:alpine

# Remove default nginx website
RUN rm -rf /usr/share/nginx/html/*

# Copy nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy all frontend files to nginx html directory
COPY . /usr/share/nginx/html/

# Create startup script to handle Railway's dynamic port
RUN cat > /docker-entrypoint.d/40-update-port.sh << 'EOF'
#!/bin/sh
# Update nginx port for Railway if PORT environment variable is set
if [ ! -z "$PORT" ]; then
    echo "Railway PORT detected: $PORT"
    sed -i "s/listen 80;/listen $PORT;/" /etc/nginx/conf.d/default.conf
else
    echo "Using default port 80"
fi
EOF

RUN chmod +x /docker-entrypoint.d/40-update-port.sh

# Set proper permissions
RUN chown -R nginx:nginx /usr/share/nginx/html && \
    chmod -R 755 /usr/share/nginx/html

# Expose port 80 (Railway will override with PORT env var)
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
