/// NSCam - IP Geolocation & Scam Tracker
/// Author - @dogesenic

use clap::Parser;
use reqwest::Client;
use serde::Deserialize;
use std::io::Write;

// ================== DATA STRUCTURES ==================

#[derive(Deserialize, Debug)]
struct IpApiResponse {
    status: String,
    message: Option<String>,
    query: String,
    isp: Option<String>,
    org: Option<String>,
    as_no: Option<String>,
    country: Option<String>,
    country_code: Option<String>,
    region_name: Option<String>,
    city: Option<String>,
    zip: Option<String>,
    lat: Option<f64>,
    lon: Option<f64>,
    timezone: Option<String>,
    mobile: Option<bool>,
    proxy: Option<bool>,
    hosting: Option<bool>,
}

#[derive(Parser, Debug)]
#[clap(author = "NScam", about = "IP Geolocation Tracker")]
struct CliArgs {
    #[clap(value_parser)]
    target: String,
}

// ================== MAIN FUNCTION ==================

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let args = CliArgs::parse();

    // Simple loading indicator
    print!("[*] Looking up {} ... ", args.target);
    std::io::stdout().flush().unwrap();

    let client = Client::builder()
        .timeout(std::time::Duration::from_secs(10))
        .build()?;

    let api_url = format!(
        "http://ip-api.com/json/{}?fields=status,message,query,isp,org,as,country,country_code,region,region_name,city,zip,lat,lon,timezone,mobile,proxy,hosting",
        args.target
    );

    let response = client.get(&api_url).send().await?;
    let data: IpApiResponse = response.json().await?;

    println!("Done");

    if data.status != "success" {
        eprintln!(
            "Error: {}",
            data.message.unwrap_or_else(|| "Invalid IP or Domain".to_string())
        );
        return Ok(());
    }

    // Determine Network Type
    let network_type = match (data.proxy, data.mobile, data.hosting) {
        (Some(true), _, _) => "Proxy / VPN",
        (_, Some(true), _) => "Mobile Data (4G/5G)",
        (_, _, Some(true)) => "Hosting Server",
        _ => "Residential IP",
    };

    // Helper to handle Option<String>
    let s = |opt: Option<String>| opt.unwrap_or_else(|| "-".to_string());

    // ================== INterface ==================
    
    println!("\n-----------------------------------");
    println!("TARGET INFORMATION");
    println!("-----------------------------------");
    println!("Target:       {}", data.query);
    println!("Type:         {}", network_type);
    println!("ISP:          {}", s(data.isp));
    println!("Organization: {}", s(data.org));
    println!("AS Number:    {}", s(data.as_no));

    println!("\n-----------------------------------");
    println!("GEOLOCATION");
    println!("-----------------------------------");
    println!("Country:      {} ({})", s(data.country), s(data.country_code));
    println!("Region:       {}", s(data.region_name));
    println!("City:         {}", s(data.city));
    println!("ZIP:          {}", s(data.zip));
    println!("Timezone:     {}", s(data.timezone));

    if let (Some(lat), Some(lon)) = (data.lat, data.lon) {
        println!("\n-----------------------------------");
        println!("MAPS");
        println!("-----------------------------------");
        println!("Coordinates:  {}, {}", lat, lon);
        println!("Google Maps:   https://www.google.com/maps/search/?api=1&query={},{}", lat, lon);
    }

    println!("\n-----------------------------------\n");

    Ok(())
}
