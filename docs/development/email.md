# Working with Email

## Catching emails in development

In the studio, you can enable relaying email to a specified SMTP endpoint.

[HELO](https://usehelo.com/) and [mailtrap](https://mailtrap.io/) are easy SMTP endpoints to set up for development that provide UIs for reviewing emails sent to all recipients. When emails are configured to be relayed to these services, they will be trapped for review and never actually delivered to anyone externally, no matter what recipients you use. As opposed to overriding recipient emails to test email features, this approach enables you to verify that personalized bulk emails send the right content to the right recipients.

### Using HELO

1. Download and open the HELO app
2. Launch studio, run `start-all`
3. Install and activate postfix email backend:

    ```bash
    enable-email
    ```

4. Configure postfix email backend to relay to EHLO app on Docker host machine:

    ```bash
    docker_host_ip="$(hab pkg exec core/busybox-static ip route|awk '/default/ { print $3 }')"
    enable-email-relay "${docker_host_ip}" 2525 studio
    ```

## Sending a test email

From the studio:

1. Install netcat:

    ```bash
    hab pkg install --binlink core/netcat
    ```

2. Open SMTP connection:

    ```bash
    nc localhost 25
    ```

3. Start SMTP session:

    ```console
    EHLO localhost.localdomain
    ```

4. Set sender:

    ```console
    MAIL FROM: <sender@example.com>
    ```

5. Set recipient:

    ```console
    RCPT TO: <recipient@example.com>
    ```

6. Set message:

    ```console
    DATA
    Subject: Hello world!

    This is the body of my email.

    Have a good day.

    .

    ```

7. Close SMTP session:

    ```console
    QUIT
    ```

8. Review postfix backend log:

    ```console
    less -S /hab/cache/sys.log
    ```
