<div class="container-login">
    <div class="wrapper-login">
        <h2>Register</h2>

        <form
            id="sign-up-form"
            method="post"
            action="/sign-up">
            <label>
                <input type="text" placeholder="Username *" name="username">
            </label>
            <span class="invalidFeedback">
                <?php echo $data['usernameError']; ?>
            </span>

            <label>
                <input type="email" placeholder="Email *" name="email">
            </label>
            <span class="invalidFeedback">
                <?php echo $data['emailError']; ?>
            </span>

            <label>
                <input type="password" placeholder="Password *" name="password">
            </label>
            <span class="invalidFeedback">
                <?php echo $data['passwordError']; ?>
            </span>

            <label>
                <input type="password" placeholder="Confirm Password *" name="confirmPassword">
            </label>
            <span class="invalidFeedback">
                <?php echo $data['confirmPasswordError']; ?>
            </span>

            <button id="submit" type="submit" value="submit">Submit</button>

            <p class="options">Already have an account? <a href="/log-in">Log in!</a></p>
        </form>
    </div>
</div>