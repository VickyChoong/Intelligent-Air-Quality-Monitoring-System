#include <ESP8266WiFi.h>        // Include the ESP8266 WiFi library
#include <DHT.h>                // Include the DHT sensor library
#include <LiquidCrystal_I2C.h>  // Include the LiquidCrystal I2C library
#include <WiFiClient.h>         // Include the WiFiClient library
#include <ESP8266HTTPClient.h>  // Include the HTTPClient library

// WiFi credentials
const char* ssid = "OPPO Reno4 Pro";
const char* password = "12345678";

// Server details
const char* serverName = "http://192.168.84.221/AirQualityMonitoring_PHP/insert_data.php";
String apiKeyValue = "tPmAT5Ab3j7F9";  // API key for authentication

#define DHTPIN D3          // Pin connected to the DHT sensor
#define DHTTYPE DHT11      // DHT sensor type
DHT dht(DHTPIN, DHTTYPE);  // Create a DHT object

LiquidCrystal_I2C lcd(0x27, 16, 2);  // Initialize the LCD

int gasPin = A0;          // Analog pin connected to the gas sensor
const int relayPin = D4;  // Digital pin connected to the relay

void setup() {
  Serial.begin(115200);        // Start serial communication at 115200 baud
  WiFi.begin(ssid, password);  // Connect to WiFi network

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  Serial.println("Connected to WiFi");
  dht.begin();      // Initialize DHT sensor
  lcd.init();       // Initialize LCD
  lcd.backlight();  // Turn on backlight
  lcd.setCursor(3, 0);
  lcd.print("Air Quality");
  lcd.setCursor(3, 1);
  lcd.print("Monitoring");
  delay(2000);
  lcd.clear();

  pinMode(relayPin, OUTPUT);    // Set relay pin as output
  digitalWrite(relayPin, LOW);  // Ensure relay is off initially
}

void loop() {
  float h = dht.readHumidity();       // Read humidity from DHT sensor
  float t = dht.readTemperature();    // Read temperature from DHT sensor
  int gasValue = analogRead(gasPin);  // Read analog value from gas sensor
  String airQuality;

  if (isnan(h) || isnan(t)) {  // Check if any reading from DHT sensor failed
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  // Determine air quality
  if (gasValue < 800) {
    airQuality = "Fresh Air";
    Serial.println("Condition: Fresh Air");
    digitalWrite(relayPin, HIGH);  // Turn on relay if air quality is good
  } else {
    airQuality = "Bad Air";
    Serial.println("Condition: Bad Air");
    digitalWrite(relayPin, LOW);  // Turn off relay if air quality is bad
  }

  // Print relay status
  Serial.print("Relay Status: ");
  Serial.println(digitalRead(relayPin) == HIGH ? "ON" : "OFF");

  // Print readings to serial monitor
  Serial.print("Humidity: ");
  Serial.print(h);
  Serial.print(" %\t");
  Serial.print("Temperature: ");
  Serial.print(t);
  Serial.print(" *C ");
  Serial.print("\tGas Value: ");
  Serial.print(gasValue);
  Serial.println(" PPM");

  // Display temperature on LCD
  lcd.setCursor(0, 0);
  lcd.print("Temperature: ");
  lcd.setCursor(0, 1);
  lcd.print(t);
  lcd.print(" C");
  delay(4000);
  lcd.clear();

  // Display humidity on LCD
  lcd.setCursor(0, 0);
  lcd.print("Humidity: ");
  lcd.setCursor(0, 1);
  lcd.print(h);
  lcd.print(" %");
  delay(4000);
  lcd.clear();

  // Display gas value and air quality on LCD
  lcd.setCursor(0, 0);
  lcd.print("Gas: ");
  lcd.print(gasValue);
  lcd.setCursor(0, 1);
  lcd.print(airQuality);
  delay(2000);
  lcd.clear();

  // Send data to server
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;

    if (http.begin(client, serverName)) {
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      String postData = "api_key=" + apiKeyValue + "&temperature=" + String(t) + "&humidity=" + String(h) + "&gas=" + String(gasValue) + "&quality=" + airQuality;
      int httpResponseCode = http.POST(postData);

      if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println(httpResponseCode);
        Serial.println(response);
      } else {
        Serial.println("Error in sending POST");
        Serial.println(httpResponseCode);
      }

      http.end();
    } else {
      Serial.println("Failed to connect to server");
    }
  }

  delay(10000);  // Send data every 10 seconds
}


