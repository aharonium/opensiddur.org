import requests
import os
import json
from slugify import slugify
from datetime import datetime
import argparse
from tqdm import tqdm
import time
from urllib.parse import unquote, urlparse

# Constants. Replace the domain name for your context
BASE_URL = 'https://[domain_name]/wp-admin/admin-post.php'
EXPORT_JSON_ACTION = 'export_json'
EXPORT_ALL_IDS_ACTION = 'export_all_ids'
HEADERS = {'User-Agent': 'Mozilla/5.0'}
RETRY_LIMIT = 5  # Number of retry attempts
RETRY_DELAY = 5  # Delay between retries in seconds
REQUEST_DELAY = 1  # Delay between consecutive requests in seconds

# Helper function to download JSON
def download_json(post_id):
    url = f"{BASE_URL}?action={EXPORT_JSON_ACTION}&post_id={post_id}&extension=.json"
    for attempt in range(RETRY_LIMIT):
        try:
            response = requests.get(url, headers=HEADERS, timeout=10)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            print(f"Error downloading JSON for ID {post_id}: {e}")
            if attempt < RETRY_LIMIT - 1:
                print(f"Retrying... ({attempt + 1}/{RETRY_LIMIT})")
                time.sleep(RETRY_DELAY)
            else:
                print(f"Failed to download JSON for ID {post_id} after {RETRY_LIMIT} attempts.")
                return None

# Helper function to create directories based on category hierarchy
def create_category_hierarchy(permalink, base_dir):
    parsed_url = urlparse(permalink)
    path_parts = parsed_url.path.strip('/').split('/')
    current_dir = os.path.join(base_dir, *path_parts[:-1])  # Exclude the last part (slug) for directory creation
    if not os.path.exists(current_dir):
        if args.verbose:
            print(f"Creating directory: {current_dir}")
        os.makedirs(current_dir, exist_ok=True)
    return current_dir

# Helper function to make names safe for directories and filenames
def safe_name(name, max_length=128):
    return slugify(name)[:max_length]

# Helper function to construct the post data similar to the output_json function
def construct_post_data(post):
    data = {
        'title': post['title'],
        'author': post['author'],
        'coauthors': post['coauthors'],
        'date': post['date'],
        'last_updated': post['last_updated'],
        'excerpt': post['excerpt'],
        'content': post['content'],
        'categories': post['categories'],
        'tags': post['tags'],
        'custom_fields': post['custom_fields'],
        'featured_image': post.get('featured_image', {}),
        'source_link': post['source_link'],
        'permalink': post['permalink'],
        'slug': post['slug']
    }
    return data

# Main function to process posts or pages
def process_items(ids, item_type):
    base_output_dir = os.path.join(output_dir, item_type)
    try:
        for i in tqdm(range(len(ids)), desc=f'Processing {item_type}', unit='item'):
            post_id = ids[i]
            post_data = download_json(post_id)
            if post_data is None:
                continue

            constructed_data = construct_post_data(post_data)
            permalink = constructed_data['permalink']
            slug = unquote(constructed_data['slug'])  # Decode the slug
            directory_path = create_category_hierarchy(permalink, base_output_dir)  # Create the directory hierarchy
            file_path = os.path.join(directory_path, f"{slug}_({post_id}).json")

            if args.verbose:
                print(f"Saving file to: {file_path}")
            with open(file_path, 'w', encoding='utf-8') as json_file:
                json.dump(post_data, json_file, ensure_ascii=False, indent=4)

            # Delay between requests to avoid overloading the server
            time.sleep(REQUEST_DELAY)
    except KeyboardInterrupt:
        print("\nProcess interrupted by user. Exiting gracefully...")

# Argument parser setup
parser = argparse.ArgumentParser(description='Download and organize JSON files from a WordPress site.')
parser.add_argument('-v', '--verbose', action='store_true', help='Enable verbose output')
args = parser.parse_args()

# Fetch all post and page IDs
print("Fetching all post and page IDs...")
response = requests.get(f"{BASE_URL}?action={EXPORT_ALL_IDS_ACTION}", headers=HEADERS)
ids_data = response.json().get('data', {})
post_ids = ids_data.get('posts', [])
page_ids = ids_data.get('pages', [])

# Output directory
timestamp = datetime.now().strftime('%Y%m%d')
output_dir = f"opensiddur.org_{timestamp}"
os.makedirs(output_dir, exist_ok=True)

# Process pages and posts
print("Processing pages...")
process_items(page_ids, 'pages')

print("Processing posts...")
process_items(post_ids, 'posts')

print("Download and organization complete.")
